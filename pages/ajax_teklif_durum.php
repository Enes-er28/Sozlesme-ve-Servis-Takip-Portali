<?php
require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();

if(isset($_POST['teklif_id'], $_POST['durum'])){
    $teklif_id = (int)$_POST['teklif_id'];
    $durum = $_POST['durum'];

    $sql = "UPDATE teklif SET durum = ? WHERE teklif_id = ?";
    $admin->pdoUpdate($sql, [$durum, $teklif_id]);

    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false, 'error'=>'Geçersiz istek']);
