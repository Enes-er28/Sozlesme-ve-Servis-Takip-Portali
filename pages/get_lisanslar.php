<?php
// Boşluk yok, bom yok
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../data/class.php';

$admin = new AdminClass();
$firma_id = $_GET['firma_id'] ?? null;

if (!$firma_id) {
    echo json_encode(['success' => false, 'message' => 'Firma ID boş']);
    exit;
}

$lisanslar = $admin->pdoQuery("
    SELECT 
      l.*, f.firma_ad, m.marka_ad, mo.model_ad
    FROM lisanslar l
    LEFT JOIN firma f ON f.firma_id = l.firma_id
    LEFT JOIN marka m ON m.marka_id = l.marka_id
    LEFT JOIN model mo ON mo.model_id = l.model_id
    WHERE l.firma_id = ?
", [$firma_id]);

echo json_encode(['success' => true, 'data' => $lisanslar], JSON_UNESCAPED_UNICODE);
exit;
