<?php
require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();



function convertDate($date){
    if(!$date) return null;
    $parts = explode('/', $date);
    if(count($parts) !== 3) return null;
    return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
}

if(isset($_POST['hareketler']) && is_array($_POST['hareketler'])){
    $hareketler = $_POST['hareketler'];

    $firma_id  = $hareketler[0]['firma_id'] ?? null;
    $fis_tarih = convertDate($hareketler[0]['fis_tarih'] ?? date('d/m/Y'));
    $detay     = $hareketler[0]['detay'] ?? null;
    $durum     = 'Beklemede';

    $sqlTeklif = "INSERT INTO teklif (firma_id, fis_tarih, detay, durum) VALUES (?,?,?,?)";
    $admin->pdoInsert($sqlTeklif, [$firma_id, $fis_tarih, $detay, $durum]);
    $teklif_id = $admin->lastInsertId();

    

    foreach($hareketler as $h){
        $admin->pdoInsert("INSERT INTO teklif_hareket 
            (marka_id, model_id, aciklama, baslangic, bitis, dongu, miktar, fiyat, indirim, indirim_er, tutar, fatura, detay, teklif_id)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)", [
            $h['marka_id'] ?? null,
            $h['model_id'] ?? null,
            $h['aciklama'] ?? null,
            convertDate($h['baslangic'] ?? null),
            convertDate($h['bitis'] ?? null),
            $h['dongu'] ?? null,
            is_numeric($h['miktar']) ? $h['miktar'] : 0,
            is_numeric($h['fiyat']) ? $h['fiyat'] : 0,
            is_numeric($h['indirim']) ? $h['indirim'] : 0,
            is_numeric($h['indirim_er']) ? $h['indirim_er'] : 0,
            is_numeric($h['tutar']) ? $h['tutar'] : 0,
            isset($h['fatura']) ? $h['fatura'] : 0,
            $h['detay'] ?? null,
            $teklif_id
        ]);
    }

    echo json_encode(['success'=>true,'message'=>'Teklif ve hareket kaydı başarılı']);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Geçersiz istek']);
exit;