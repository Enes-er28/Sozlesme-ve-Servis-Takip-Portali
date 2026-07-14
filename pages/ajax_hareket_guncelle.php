<?php
require_once __DIR__ . '/../data/class.php';

$admin = new AdminClass();

function convertDate($date){
    if(!$date) return null;
    $parts = explode('/', $date); // dd/mm/yyyy
    if(count($parts) !== 3) return null;
    return $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // yyyy-mm-dd
}

if (isset($_POST['hareketler']) && is_array($_POST['hareketler'])) {

    foreach ($_POST['hareketler'] as $h) {

        $id = (int)($h['abone_hizmet_hareket_id'] ?? 0);
        $abone_hizmet_id = (int)($h['abone_hizmet_id'] ?? 0);

        // Normalize
        $marka_id   = !empty($h['marka_id']) ? $h['marka_id'] : null;
        $model_id   = !empty($h['model_id']) ? $h['model_id'] : null;
        $aciklama   = $h['aciklama'] ?? null;
        $baslangic  = convertDate($h['baslangic'] ?? null);
        $bitis      = convertDate($h['bitis'] ?? null);
        $dongu      = $h['dongu'] ?? null;

        $miktar     = is_numeric($h['miktar']) ? (float)$h['miktar'] : 0;
        $fiyat      = is_numeric($h['fiyat']) ? (float)$h['fiyat'] : 0;
        $indirim    = is_numeric($h['indirim']) ? (float)$h['indirim'] : 0;
        $indirim_er = is_numeric($h['indirim_er']) ? (float)$h['indirim_er'] : 0;

        $fatura     = isset($h['fatura']) ? (int)$h['fatura'] : 0;
        $detay      = $h['detay'] ?? null;

        // 🔥 BACKEND ZİNCİRLEME İNDİRİM HESABI
        $brut = $miktar * $fiyat;
        $tutar = $brut;

        if ($indirim > 0) {
            $tutar -= $tutar * $indirim / 100;
        }
        if ($indirim_er > 0) {
            $tutar -= $tutar * $indirim_er / 100;
        }

        $tutar = round($tutar, 2);

        if ($id > 0) {
            // UPDATE
            $sql = "
                UPDATE abone_hizmet_hareket SET
                    marka_id=?,
                    model_id=?,
                    aciklama=?,
                    baslangic=?,
                    bitis=?,
                    dongu=?,
                    miktar=?,
                    fiyat=?,
                    indirim=?,
                    indirim_er=?,
                    tutar=?,
                    fatura=?,
                    detay=?
                WHERE abone_hizmet_hareket_id=?
            ";

            $args = [
                $marka_id,
                $model_id,
                $aciklama,
                $baslangic,
                $bitis,
                $dongu,
                $miktar,
                $fiyat,
                $indirim,
                $indirim_er,
                $tutar,
                $fatura,
                $detay,
                $id
            ];

            $admin->pdoUpdate($sql, $args);

        } else {
            // INSERT
            $sql = "
                INSERT INTO abone_hizmet_hareket
                (
                    marka_id, model_id, aciklama,
                    baslangic, bitis, dongu,
                    miktar, fiyat,
                    indirim, indirim_er,
                    tutar, fatura, detay,
                    abone_hizmet_id
                )
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ";

            $args = [
                $marka_id,
                $model_id,
                $aciklama,
                $baslangic,
                $bitis,
                $dongu,
                $miktar,
                $fiyat,
                $indirim,
                $indirim_er,
                $tutar,
                $fatura,
                $detay,
                $abone_hizmet_id
            ];

            $admin->pdoInsert($sql, $args);
        }
    }

    echo '<div class="alert alert-success">Güncelleme başarılı</div>';
}
