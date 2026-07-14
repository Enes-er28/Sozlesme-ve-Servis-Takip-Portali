<?php
// Gereksiz output varsa temizle (bom, boşluk vb)
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../data/class.php';

$admin = new AdminClass();

$musteri_id = $_GET['musteri_id'] ?? null;

if (!$musteri_id) {
    echo json_encode(['success' => false, 'message' => 'Müşteri ID boş']);
    exit;
}

// Envanter bilgilerini müşteri_id'ye göre çekiyoruz
$envanterler = $admin->pdoQuery("
    SELECT 
    e.envanter_id,
    e.cihaz_turu,
    e.marka,
    e.model,
    e.islemci,
    e.bellek,
    e.disk,
    e.isletim_sistemi,
    e.uygulamalar,
    e.bilgi,
    f.firma_ad,
    s.sube_ad,
    m.musteri_ad,
    m.musteri_soyad
FROM envanter e
LEFT JOIN musteri m ON m.musteri_id = e.musteri_id
LEFT JOIN sube s ON s.sube_id = m.sube_id
LEFT JOIN firma f ON f.firma_id = s.firma_id
WHERE e.musteri_id = ?

", [$musteri_id]);

if ($envanterler && count($envanterler) > 0) {
    // Örnek olarak sadece ilk envanteri gönderiyoruz,
    // dilersen tümünü dönebilirsin (array olarak)
    echo json_encode([
        'success' => true,
        'data' => $envanterler[0]  // ilk kayıt
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => 'Envanter bulunamadı']);
}
exit;
