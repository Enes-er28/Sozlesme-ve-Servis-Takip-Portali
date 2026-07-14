<?php
include_once 'class.php'; // PDO bağlantını içeren sınıf

header('Content-Type: application/json');

if(!isset($_POST['id']) || empty($_POST['id'])){
    echo json_encode(['success'=>false,'error'=>'ID gelmedi!']);
    exit;
}

$id = (int)$_POST['id'];

try {
    $sql = "DELETE FROM teklif_hareket WHERE teklif_hareket_id = ?";
    $admin = new AdminClass(); // PDO sınıfı
    $admin->pdoDelete($sql, [$id]);

    echo json_encode(['success'=>true]);

} catch (Exception $e){
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
