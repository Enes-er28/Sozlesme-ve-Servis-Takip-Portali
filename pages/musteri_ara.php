<?php
    ob_clean(); // varsa tamponu temizle
    header('Content-Type: application/json; charset=utf-8');

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    require_once __DIR__ . '/../data/class.php';
    $admin = new AdminClass();

    $q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!$q) {
    echo json_encode(['success' => false, 'message' => 'Boş arama']);
    exit;
}

try {
    $results = $admin->musteriAra($q);

    foreach ($results as &$musteri) {
    if (isset($musteri['firma_id'])) {
        $firmaBilgi = $admin->getFirmaModellerVeDurumById($musteri['firma_id']);
        $musteri['hizmet_durum'] = $firmaBilgi['hizmet_durum'] ?? 'Bilinmiyor';
        $musteri['modeller'] = $firmaBilgi['modeller'] ?? '-';
    } else {
        $musteri['hizmet_durum'] = 'Bilinmiyor';
        $musteri['modeller'] = '-';
    }
}

    // DEBUG: bak bakalım ne geliyor
    file_put_contents("debug.txt", print_r($results, true));

    

    if (!empty($results)) {
        echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_UNICODE);   
    } else {
        echo json_encode(['success' => false, 'message' => 'Bulunamadı']);
    }
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
}
exit;