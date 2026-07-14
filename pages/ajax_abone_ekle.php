<?php
require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();

header('Content-Type: application/json');

if(!isset($_POST['teklif_id'])){
    echo json_encode(['success'=>false,'error'=>'Teklif ID yok']);
    exit;
}

$teklif_id = (int)$_POST['teklif_id'];

// Teklifi çek
$teklif = $admin->pdoQuery("SELECT * FROM teklif WHERE teklif_id = ?", [$teklif_id]);
$teklif = $teklif[0] ?? null;

if(!$teklif){
    echo json_encode(['success'=>false,'error'=>'Teklif bulunamadı']);
    exit;
}

try {
    // 1. Teklifi abone_hizmet tablosuna aktar
    $sqlAbone = "INSERT INTO abone_hizmet (firma_id, fis_tarih, detay, durum, aktif) VALUES (?,?,?,?,?)";
    $admin->pdoInsert($sqlAbone, [
        $teklif['firma_id'] ?? null,
        $teklif['fis_tarih'] ?? null,
        $teklif['detay'] ?? null,
        'Aktif',   // durum varchar olduğu için string
        1          // aktif tinyint
    ]);;

    $abone_hizmet_id = $admin->lastInsertId();

    // 2. Teklif hareketlerini abone_hizmet_harekete aktar
    $hareketler = $admin->pdoQuery("SELECT * FROM teklif_hareket WHERE teklif_id = ?", [$teklif_id]);

    foreach($hareketler as $h){
        $admin->pdoInsert("INSERT INTO abone_hizmet_hareket 
            (marka_id, model_id, aciklama, baslangic, bitis, dongu, miktar, fiyat, tutar, fatura, detay, abone_hizmet_id,indirim, indirim_er)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
            $h['marka_id'] ?? null,
            $h['model_id'] ?? null,
            $h['aciklama'] ?? null,
            $h['baslangic'] ?? null,
            $h['bitis'] ?? null,
            $h['dongu'] ?? null,
            $h['miktar'] ?? 0,
            $h['fiyat'] ?? 0,
            $h['tutar'] ?? 0,
            $h['fatura'] ?? 0,
            $h['detay'] ?? null,
            $abone_hizmet_id,
            $h['indirim'] ?? 0,        // ✅ YENİ
            $h['indirim_er'] ?? 0
        ]);
    }

    // 3. Teklif durumunu "Kabul Edildi" olarak güncelle
    $sql = "UPDATE teklif SET durum = ? WHERE teklif_id = ?";
    $admin->pdoUpdate($sql, ['Kabul Edildi', $teklif_id]);

    echo json_encode([
        'success'=>true,
        'message'=>'Teklif aktarıldı ve kabul edildi',
        'abone_hizmet_id' => $abone_hizmet_id
    ]);
} catch (Exception $e){
    echo json_encode(['success'=>false,'error'=>'Hata: '.$e->getMessage()]);
}

exit;
