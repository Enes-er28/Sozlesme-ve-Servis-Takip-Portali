<?php
// Hata basımını kapat, temiz JSON dön
while (ob_get_level() > 0) { ob_end_clean(); }
ini_set('display_errors', 1); 
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// Veritabanı Bağlantısı
$host     = 'ERSRV\SQLEXPRESS'; 
$dbname   = 'Erportal';
$username = 'sa';
$password = 'logo';

try {
    $dsn = "sqlsrv:Server=$host;Database=$dbname;TrustServerCertificate=true";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["durum" => "hata", "mesaj" => "DB Baglanti Hatasi"]);
    exit;
}

$islem = $_GET['islem'] ?? '';

// =========================================================================
// 🌟 YENİ: LOGLAMA SİSTEMİ (TÜM GELEN İSTEKLERİ KAYDEDER)
// =========================================================================
$logMesaji = date('Y-m-d H:i:s') . " | ISLEM: $islem | POST VERILERI: " . json_encode($_POST) . "\n";
file_put_contents('cagri_log.txt', $logMesaji, FILE_APPEND);
// =========================================================================

switch ($islem) {
    // 1. CİHAZ PING ATTI (Cihaz durumunu Kullanıcı ID ile günceller)
    case 'ping_at':
        $kullanici_id = $_POST['kullanici_id'] ?? 0;
        $cihaz_tipi = $_POST['cihaz_tipi'] ?? ''; // 'mobil' veya 'pc'

        if ($kullanici_id > 0 && ($cihaz_tipi == 'mobil' || $cihaz_tipi == 'pc')) {
            // Kullanıcı tabloda var mı?
            $kontrol = $pdo->prepare("SELECT kullanici_id FROM kullanici_cihaz_durum WHERE kullanici_id = ?");
            $kontrol->execute([$kullanici_id]);
            
            if ($kontrol->rowCount() == 0) {
                $pdo->prepare("INSERT INTO kullanici_cihaz_durum (kullanici_id) VALUES (?)")->execute([$kullanici_id]);
            }

            // Ping zamanını güncelle
            $kolon = ($cihaz_tipi == 'mobil') ? 'mobil_son_sinyal' : 'pc_son_sinyal';
            $sql = "UPDATE kullanici_cihaz_durum SET $kolon = GETDATE() WHERE kullanici_id = ?";
            $pdo->prepare($sql)->execute([$kullanici_id]);

            // Mobilin durumunu geri döndür
            $durumSorgu = $pdo->prepare("SELECT mobil_son_sinyal FROM kullanici_cihaz_durum WHERE kullanici_id = ?");
            $durumSorgu->execute([$kullanici_id]);
            $sonuc = $durumSorgu->fetch(PDO::FETCH_ASSOC);

            // Gelen tarihi formatla
            $mobil_sinyal = $sonuc['mobil_son_sinyal'] ? date('Y/m/d H:i:s', strtotime($sonuc['mobil_son_sinyal'])) : null;

            echo json_encode(["durum" => "basarili", "mobil_son_sinyal" => $mobil_sinyal]);
        } else {
            echo json_encode(["durum" => "hata", "mesaj" => "Eksik parametre."]);
        }
        break;

    // 2. TELEFON ÇALDI!
    case 'cagri_geldi':
        $numara = $_POST['numara'] ?? '';
        $kullanici_id = $_POST['kullanici_id'] ?? 0;

        if (!empty($numara) && $kullanici_id > 0) {
            try {
                $sql = "INSERT INTO aktif_cagrilar (arayan_numara, aranan_personel_isim, durum, hedef_kullanici_id, olusturma_tarihi) 
                        VALUES (?, 'Mobil Uygulama', 'Bekliyor', ?, GETDATE())";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$numara, $kullanici_id]);
                echo json_encode(["durum" => "basarili"]);
                
                // Başarılı kaydı da logla
                file_put_contents('cagri_log.txt', date('Y-m-d H:i:s') . " | BASARILI KAYIT: Numara: $numara, ID: $kullanici_id\n", FILE_APPEND);

            } catch (PDOException $e) {
                // Hata varsa log dosyasına özel olarak yaz
                file_put_contents('cagri_log.txt', date('Y-m-d H:i:s') . " | SQL HATASI: " . $e->getMessage() . "\n", FILE_APPEND);
                echo json_encode(["durum" => "hata", "mesaj" => $e->getMessage()]);
            }
        } else {
            echo json_encode(["durum" => "hata", "mesaj" => "Numara veya ID gecersiz."]);
        }
        break;

    // 3. PC BİLDİRİM KONTROLÜ (PC sadece kendi ID'sine gelen çağrıları sorar)
    case 'cagri_kontrol':
        $kullanici_id = $_POST['kullanici_id'] ?? 0;
        
        if ($kullanici_id > 0) {
            $sql = "SELECT TOP 1 * FROM aktif_cagrilar 
                    WHERE hedef_kullanici_id = ? AND durum = 'Bekliyor' 
                    ORDER BY olusturma_tarihi DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$kullanici_id]);
            $cagri = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cagri) {
                $pdo->prepare("UPDATE aktif_cagrilar SET durum = 'Ekrana_Dustu' WHERE cagri_id = ?")->execute([$cagri['cagri_id']]);
                
                $musteriSorgu = $pdo->prepare("SELECT musteri_ad, musteri_soyad FROM musteri WHERE telefon LIKE ? OR telefon LIKE ?");
                $telKisa = "%" . substr($cagri['arayan_numara'], -10);
                $musteriSorgu->execute(["%" . $cagri['arayan_numara'] . "%", $telKisa]);
                $musteri = $musteriSorgu->fetch(PDO::FETCH_ASSOC);

                $isim = $musteri ? ($musteri['musteri_ad'] . ' ' . $musteri['musteri_soyad']) : 'Kayıtsız Numara';

                echo json_encode([
                    "durum" => "cagri_var", 
                    "numara" => $cagri['arayan_numara'],
                    "isim" => $isim
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["durum" => "yok"]);
            }
        } else {
            echo json_encode(["durum" => "hata", "mesaj" => "Kullanici ID gecersiz."]);
        }
        break;

    default:
        echo json_encode(["durum" => "hata", "mesaj" => "Gecersiz islem"]);
        break;
}
exit;
?>