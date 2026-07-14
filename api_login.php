<?php
// Sadece JSON döneceğimizi belirtiyoruz
header('Content-Type: application/json; charset=utf-8');

// Senin sınıfını çağırıyoruz
include_once 'data/class.kullanicilar.php'; 
$app = new AdminKullaniciClass();

// Sadece POST isteği geldiyse çalışsın
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Flutter'dan gelen verileri al
    $kullanici_adi = isset($_POST['kullanici_adi']) ? trim($app->getSecurity($_POST['kullanici_adi'])) : '';
    $password      = isset($_POST['sifre']) ? trim($_POST['sifre']) : '';

    if (empty($kullanici_adi) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Kullanıcı adı veya şifre boş olamaz."]);
        exit;
    }

    $users = $app->getUser($kullanici_adi);

    if (!$users || !is_array($users)) {
        echo json_encode(["success" => false, "message" => "Kullanıcı bulunamadı."]);
        exit;
    }

    // Şifre kontrolü
    if (isset($users['sifre']) && password_verify($password, $users['sifre'])) {
        
        // Durum (Aktiflik) kontrolü
        if (!empty($users['durum']) && strtolower($users['durum']) === 'aktif') {
            
            // HER ŞEY BAŞARILI! Flutter'a kullanıcı bilgilerini JSON olarak yolla
            echo json_encode([
                "success"       => true,
                "message"       => "Giriş başarılı",
                "kullanici_id"  => $users['id'],
                "kullanici_adi" => $users['kullanici_adi'],
                "isim"          => $users['isim'] ?? $users['kullanici_adi'], // İsim boşsa kullanıcı adını yolla
                "rol"           => $users['rol'] ?? 'Misafir'
            ], JSON_UNESCAPED_UNICODE);
            exit;

        } else {
            echo json_encode(["success" => false, "message" => "Hesabınız aktif değil. Yöneticinize başvurun."]);
            exit;
        }
    } else {
        echo json_encode(["success" => false, "message" => "Şifre yanlış."]);
        exit;
    }
} else {
    // Post isteği değilse hata dön
    echo json_encode(["success" => false, "message" => "Geçersiz istek yöntemi."]);
    exit;
}
?>