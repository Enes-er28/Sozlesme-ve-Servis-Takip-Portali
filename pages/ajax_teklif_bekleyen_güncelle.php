<?php
require_once __DIR__ . '/../data/class.php';

$admin = new AdminClass();

function convertDate($date){
    if(!$date) return null;
    $parts = explode('/', $date); // dd/mm/yyyy
    if(count($parts) !== 3) return null;
    return $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // yyyy-mm-dd
}

if(isset($_POST['hareketler'])){
    $hareketler = $_POST['hareketler'];

    foreach($hareketler as $h){
        $id        = isset($h['teklif_hareket_id']) ? (int)$h['teklif_hareket_id'] : 0; 
        $teklif_id = $h['teklif_id'] ?? null;

        // Boş değerleri normalize et
        $marka_id  = !empty($h['marka_id']) ? $h['marka_id'] : null;
        $model_id  = !empty($h['model_id']) ? $h['model_id'] : null;
        $aciklama  = $h['aciklama'] ?? null;
        $baslangic = convertDate($h['baslangic'] ?? null);
        $bitis     = convertDate($h['bitis'] ?? null);
        $dongu     = $h['dongu'] ?? null;
        $miktar    = is_numeric($h['miktar']) ? $h['miktar'] : 0;
        $fiyat     = is_numeric($h['fiyat']) ? $h['fiyat'] : 0;
        $tutar     = is_numeric($h['tutar']) ? $h['tutar'] : 0;
        $fatura    = isset($h['fatura']) ? (int)$h['fatura'] : 0;
        $detay     = $h['detay'] ?? null;

        if($id > 0){
            // UPDATE
            $sql = "UPDATE teklif_hareket
                    SET marka_id=?, model_id=?, aciklama=?, baslangic=?, bitis=?, 
                        dongu=?, miktar=?, fiyat=?, tutar=?, fatura=?, detay=?
                    WHERE teklif_hareket_id=?";
            $args = [$marka_id,$model_id,$aciklama,$baslangic,$bitis,$dongu,
                     $miktar,$fiyat,$tutar,$fatura,$detay,$id];
            $admin->pdoUpdate($sql,$args);

        } else {
            // INSERT
            $sql = "INSERT INTO teklif_hareket
                    (marka_id, model_id, aciklama, baslangic, bitis, dongu, 
                     miktar, fiyat, tutar, fatura, detay, teklif_id)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $args = [
                $marka_id,
                $model_id,
                $aciklama,
                $baslangic,
                $bitis,
                $dongu,
                $miktar,
                $fiyat,
                $tutar,
                $fatura,
                $detay,
                $teklif_id
            ];

            $admin->pdoInsert($sql, $args);
        }
    }

    echo '<div class="alert alert-success">Güncelleme başarılı</div>';
}
