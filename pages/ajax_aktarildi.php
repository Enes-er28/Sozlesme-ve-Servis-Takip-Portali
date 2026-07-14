<?php
require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();

if (isset($_POST['hareketler']) && is_array($_POST['hareketler'])) {
    $hareketler = $_POST['hareketler'];

    foreach ($hareketler as $h) {
        if (!empty($h['abone_hizmet_id'])) {
            $admin->pdoQuery(
                "UPDATE abone_hizmet SET durum = 'aktarildi' WHERE abone_hizmet_id = ?",
                [$h['abone_hizmet_id']]
            );
        }
    }

    echo json_encode(['success' => true, 'message' => 'Durum aktarildi olarak güncellendi']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Geçersiz istek']);
exit;
