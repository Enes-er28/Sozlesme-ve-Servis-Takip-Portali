<?php
// 1. TEMİZLİK VE AYARLAR
while (ob_get_level() > 0) { ob_end_clean(); }
ini_set('display_errors', 0); 
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// 2. VERİTABANI BAĞLANTISI
$host     = 'ERSRV\SQLEXPRESS'; 
$dbname   = 'Erportal';
$username = 'sa';
$password = 'logo';

try {
    $dsn = "sqlsrv:Server=$host;Database=$dbname;TrustServerCertificate=true";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["durum" => "hata", "mesaj" => "DB Baglanti Hatasi: " . $e->getMessage()]);
    exit;
}

// ========================================================================
// 3. YÖNLENDİRİCİ (ROUTER) - Flutter'dan gelen emri burası karşılar
// ========================================================================
$islem = $_GET['islem'] ?? ''; 

switch ($islem) {
    file_put_contents('cagri_log.txt', date('Y-m-d H:i:s') . " - Gelen: " . json_encode($_POST) . "\n", FILE_APPEND);
    case 'cagri_geldi': // FLUTTER'DAN GELEN KRİTİK KOMUT
            cagriKaydet($pdo);
            break;

    case 'destek_listesi':
        getDestekListesi($pdo);
        break;

    case 'musteri_listesi':
        getMusteriListesi($pdo);
        break;
        
    case 'kullanici_listesi':
        getKullaniciListesi($pdo);
        break;

    // Yeni bir işlem eklemek istersen buraya case açacaksın
    // case 'fatura_listesi':
    //     getFaturaListesi($pdo);
    //     break;

    default:
        echo json_encode(["durum" => "hata", "mesaj" => "Gecersiz islem turu."]);
        break;
}
exit; // İşlem bitince sayfayı kapat

// ========================================================================
// 4. FONKSİYONLAR (Bütün sorguların burada toplanacak)
// ========================================================================
//-------------------------------------------------------------------------------------------------
//  Ana sayfa destek fonksiyonları

function getDestekListesi($pdo) {
    try {
        // Senin orjinal JOIN'li mükemmel sorgun (Telefon için TOP 50 eklendi)
        $sql = "SELECT TOP 50
                    d.destek_id, d.eposta, d.telefon, d.ariza, 
                    d.yapilan_islem, d.sonuc, d.islemi_yapan_personel, 
                    d.aktarilacak_personel, d.planlanan_tarih, 
                    d.ise_gidecek_persone, d.note, d.olusturma_tarihi, 
                    f.firma_ad, f.excel_id, s.sube_ad, m.musteri_ad, d.durum
                FROM admind d
                LEFT JOIN firma f ON d.firma_id = f.firma_id
                LEFT JOIN sube s ON d.sube_id = s.sube_id
                LEFT JOIN musteri m ON d.musteri_id = m.musteri_id
                ORDER BY d.olusturma_tarihi DESC"; 
                
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["durum" => "baglandi", "veriler" => $data], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(["durum" => "hata", "mesaj" => $e->getMessage()]);
    }
}

function getKullaniciListesi($pdo) {
    try {
        $sql = "SELECT TOP 5 * FROM kullanici"; 
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["durum" => "baglandi", "veriler" => $data], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(["durum" => "hata", "mesaj" => $e->getMessage()]);
    }
}

function cagriKaydet($pdo) {
    try {
        // Flutter'dan POST ile gelen verileri alıyoruz
        $numara = $_POST['numara'] ?? '';
        $kullanici_id = $_POST['kullanici_id'] ?? 0;

        if (empty($numara)) {
            echo json_encode(["durum" => "hata", "mesaj" => "Numara bos gelemez."]);
            return;
        }

        // Gelen numarayı temizleyelim (başındaki +90 vs. varsa standart hale getirmek gerekebilir)
        $numara = str_replace([' ', '-', '(', ')', '+'], '', $numara);

        // Cagri tablosuna ekleyelim (Tablo adını 'gelen_cagrilar' varsaydım, senin yapına göre değiştir)
        // Bu tabloyu web panelin saniyede 1 kontrol etmeli ki ekrana popup açsın.
        $sql = "INSERT INTO gelen_cagrilar (numara, kullanici_id, tarih, durum) VALUES (?, ?, GETDATE(), 0)";
        $stmt = $pdo->prepare($sql);
        $sonuc = $stmt->execute([$numara, $kullanici_id]);

        if ($sonuc) {
            echo json_encode(["durum" => "basarili", "mesaj" => "Cagri kaydedildi."]);
        } else {
            echo json_encode(["durum" => "hata", "mesaj" => "Kayit yapilamadi."]);
        }

    } catch (Exception $e) {
        echo json_encode(["durum" => "hata", "mesaj" => $e->getMessage()]);
    }
}

function getMusteriListesi($pdo) {
    try {
        $sql = "SELECT m.*, s.sube_ad, f.firma_ad, f.firma_id
                FROM musteri m
                JOIN sube s ON m.sube_id = s.sube_id
                JOIN firma f ON s.firma_id = f.firma_id
                ORDER BY m.musteri_id DESC";
                
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["durum" => "baglandi", "veriler" => $data], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(["durum" => "hata", "mesaj" => $e->getMessage()]);
    }
}



?>