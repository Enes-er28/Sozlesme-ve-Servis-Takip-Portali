<?php
class AdminClass {
    protected $pdo = null;
    protected $host = 'ENES-LAPTOP\SQLEXPRESS'; // SQL Server Instance adını buraya yaz
    protected $dbname = 'Erportal'; // MS SQL'deki veritabanı adı  
    protected $username = 'sa';
    protected $password = '123';
/*
    class AdminClass {
    protected $pdo = null;
    protected $host = 'ERSRV\SQLEXPRESS'; // SQL Server Instance adını buraya yaz
    protected $dbname = 'Erportal'; // MS SQL'deki veritabanı adı  
    protected $username = 'sa';
    protected $password = 'logo';
*/
    public function __construct() {
        try {
            $this->pdo = new PDO(
                "sqlsrv:Server=$this->host;Database=$this->dbname",
                $this->username,
                $this->password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $error) {
            die("Bağlantı hatası: " . $error->getMessage());
        }

        if(!isset($_SESSION['kullanici_adi']) && !isset($_SESSION['login'])) {
            header('location: ./login.php');
            exit;
        }
    }

    public function pdoPrepare($sql,$args=[])  {
        $statment = $this->pdo->prepare($sql);
        $response = $statment->execute($args);
        if ($response) {
            return $response;
        }else {return false;}
    }

    public function pdoInsert($sql, $args) {
        $statment = $this->pdo->prepare($sql);
        $response = $statment->execute($args);
        if ($response) {
            return '<div class="alert alert-success">İşlem Başarılı...</div>';
        } else {
            return '<div class="alert alert-danger">İşlem Başarısız...</div>';
        }
    }

    public function pdoDelete($sql, $args) {
        $statment = $this->pdo->prepare($sql);
        $response = $statment->execute($args);
        if ($response) {
            return '<div class="alert alert-success">Silme İşlemi Başarılı...</div>';
        } else {
            return '<div class="alert alert-danger">Silme İşlemi Başarısız...</div>';
        }
    }

    public function pdoUpdate($sql, $args) {
    $stmt = $this->pdo->prepare($sql);
    $result = $stmt->execute($args);

    if ($result) {
        return '<div class="alert alert-success">Güncelleme Başarılı...</div>';
    } else {
        return '<div class="alert alert-danger">Güncelleme Başarısız...</div>';
    }
}


    public function beginTransaction() {
    return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    public function getPdo() {
        return $this->pdo;
    }
    public function pdoSelect($sql, $args = []) {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }   

    


    public function firma_Bilgi() {
        $sql = $this->pdo->query("SELECT * FROM firma ORDER BY firma_ad ASC", PDO::FETCH_ASSOC)->fetchAll();
        if ($sql) {
            return $sql;
        } else {
            return false;
        }
    }

    public function firma_Bilgi_Filtreli($harf = 'TÜMÜ') {
        $sql = "SELECT * FROM firma";
        
        // Harf TÜMÜ değilse, SQL sorgusuna filtre ekle
        if ($harf !== 'TÜMÜ' && !empty($harf)) {
            $sql .= " WHERE firma_ad LIKE :harf";
        }
        
        $sql .= " ORDER BY firma_id DESC";

        $stmt = $this->pdo->prepare($sql);

        // Güvenli parametre bağlama
        if ($harf !== 'TÜMÜ' && !empty($harf)) {
            $harf_param = $harf . '%'; // Örn: 'A' ile başlayanlar
            $stmt->bindParam(':harf', $harf_param, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // mail için
    public function firmaAdiGetir($firma_id) {
        $sql = "SELECT firma_ad FROM firma WHERE firma_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$firma_id]);
        return $stmt->fetchColumn();
    }

    public function musteriAdiGetir($musteri_id) {
        $sql = "SELECT CONCAT(musteri_ad, ' ', musteri_soyad) 
                FROM musteri 
                WHERE musteri_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$musteri_id]);
        return $stmt->fetchColumn();
    }


    public function getSecurity($data) {
        if (is_array($data)) {
            $variable = array_map('htmlspecialchars', $data);
            $response = array_map('stripslashes', $variable);
            return $response;

        } else {
            $variable = htmlspecialchars($data);
            $response = stripslashes($variable);
            return $response;
        }
    }

    public function getSubeBilgi() {
    $sql = "SELECT * FROM sube ORDER BY sube_id DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getCihazBilgi() {
    $sql = "SELECT * FROM cihaz_turu ORDER BY cihaz_id DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getSistemBilgi() {
    $sql = "SELECT * FROM isletim_sistemi ORDER BY isletim_sistemi_id DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getMusteriBilgi() {
        $sql = "SELECT 
                m.*, 
                s.sube_ad, 
                f.firma_ad, 
                f.firma_id
                FROM musteri m
                JOIN sube s ON m.sube_id = s.sube_id
                JOIN firma f ON s.firma_id = f.firma_id
                ORDER BY m.musteri_id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMusteriBilgi_Filtreli($harf = 'TÜMÜ') {
        $sql = "SELECT 
                m.*, 
                s.sube_ad, 
                f.firma_ad, 
                f.firma_id
                FROM musteri m
                JOIN sube s ON m.sube_id = s.sube_id
                JOIN firma f ON s.firma_id = f.firma_id";

        // Harf TÜMÜ değilse, SQL sorgusuna filtre ekle
        if ($harf !== 'TÜMÜ' && !empty($harf)) {
            $sql .= " WHERE m.musteri_ad LIKE :harf";
        }

        $sql .= " ORDER BY m.musteri_id DESC";

        $stmt = $this->pdo->prepare($sql);

        // Parametre bağlama
        if ($harf !== 'TÜMÜ' && !empty($harf)) {
            $harf_param = $harf . '%';
            $stmt->bindParam(':harf', $harf_param, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSubeBilgiByFirmaId($firma_id) {
        $sql = "SELECT * FROM sube WHERE firma_id = ? ORDER BY sube_ad";
        return $this->pdoQuery($sql, [$firma_id]);
    }

        // Marka bilgileri
    public function getMarkalar() {
        $sql = "SELECT * FROM marka ORDER BY marka_ad ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Model bilgileri
    public function getModeller() {
    $sql = "SELECT m.*, ma.marka_ad FROM model m JOIN marka ma ON m.marka_id = ma.marka_id ORDER BY m.model_id DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Model bilgilerini marka_id'ye göre getir
    public function getModellerByMarkaId($marka_id) {
        $sql = "SELECT * FROM model WHERE marka_id = ? ORDER BY model_ad ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$marka_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function getTurler() {
        return $this->pdo->query("SELECT * FROM hizmet_tur ORDER BY tur_ad")->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getLastInsertId() {
    return $this->pdo->lastInsertId();
    }



    public function pdoQuery($sql, $args = []) {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($args);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getLisanslar() {
    $sql = "
        SELECT 
            l.lisans_id,
            l.firma_id,
            f.firma_ad,
            m.marka_adi,
            mo.model_adi,
            l.kullanici_adi,
            l.kullanici_sifre,
            l.license_key
        FROM lisanslar l
        LEFT JOIN firma f ON f.firma_id = l.firma_id
        LEFT JOIN marka m ON m.marka_id = l.marka_id
        LEFT JOIN model mo ON mo.model_id = l.model_id
        ORDER BY l.lisans_id DESC
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getLisansById($lisans_id) {
    $sql = "
        SELECT 
            l.lisans_id,
            l.firma_id,
            f.firma_ad,
            m.marka_adi,
            mo.model_adi,
            l.kullanici_adi,
            l.kullanici_sifre,
            l.license_key
        FROM lisanslar l
        LEFT JOIN firma f ON f.firma_id = l.firma_id
        LEFT JOIN marka m ON m.marka_id = l.marka_id
        LEFT JOIN model mo ON mo.model_id = l.model_id
        WHERE l.lisans_id = :lisans_id
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':lisans_id' => $lisans_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    // ✅ Yeni lisans ekleme
    public function insertLisans($data) {
        $sql = "
            INSERT INTO lisanslar 
                (firma_id, marka_id, model_id, kullanici_adi, kullanici_sifre, license_key)
            VALUES 
                (:firma_id, :marka_id, :model_id, :kullanici_adi, :kullanici_sifre, :license_key)
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':firma_id'        => $data['firma_id'],
            ':marka_id'           => $data['marka_id'],
            ':model_id'           => $data['model_id'],
            ':kullanici_adi'   => $data['kullanici_adi'],
            ':kullanici_sifre' => $data['kullanici_sifre'],
            ':license_key'     => $data['license_key']
        ]);
    }

    // ✅ Lisans güncelleme
    public function updateLisans($data) {
        $sql = "
            UPDATE lisanslar SET 
                firma_id = :firma_id,
                marka_id = :marka_id,
                model_id = :model_id,
                kullanici_adi = :kullanici_adi,
                kullanici_sifre = :kullanici_sifre,
                license_key = :license_key
            WHERE lisans_id = :lisans_id
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':firma_id'        => $data['firma_id'],
            ':marka_id'           => $data['marka_id'],
            ':model_id'           => $data['model_id'],
            ':kullanici_adi'   => $data['kullanici_adi'],
            ':kullanici_sifre' => $data['kullanici_sifre'],
            ':license_key'     => $data['license_key'],
            ':lisans_id'       => $data['lisans_id']
        ]);
    }

    // ✅ Lisans silme
    public function deleteLisans($lisans_id) {
        $sql = "DELETE FROM lisanslar WHERE lisans_id = :lisans_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':lisans_id' => $lisans_id]);
    } 

    
        
public function destek_Bilgi($baslangic = null, $bitis = null)
{
    $sql = "SELECT 
                d.destek_id, d.firma_id, d.eposta, d.telefon, d.ariza, 
                d.yapilan_islem, d.sonuc, d.islemi_yapan_personel, 
                d.aktarilacak_personel, d.planlanan_tarih, 
                d.ise_gidecek_persone, d.note, d.olusturma_tarihi, 
                f.firma_ad, f.excel_id, s.sube_ad, m.musteri_ad, d.durum
            FROM admind d
            LEFT JOIN firma f ON d.firma_id = f.firma_id
            LEFT JOIN sube s ON d.sube_id = s.sube_id
            LEFT JOIN musteri m ON d.musteri_id = m.musteri_id
            WHERE 1=1";

    $params = [];

    if (!empty($baslangic)) {
        $sql .= " AND CAST(d.olusturma_tarihi AS DATE) >= ?";
        $params[] = $baslangic;
    }

    if (!empty($bitis)) {
        $sql .= " AND CAST(d.olusturma_tarihi AS DATE) <= ?";
        $params[] = $bitis;
    }

    $sql .= " ORDER BY d.olusturma_tarihi DESC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}





public function getDestekById($id) {
    $sql = "
        SELECT admind.*, firma.excel_id
        FROM admind
        LEFT JOIN firma ON firma.firma_id = admind.firma_id
        WHERE admind.destek_id = :id
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}





        
    public function destek_Bilgi3() {
    $sql = "SELECT 
                d.destek_id, d.eposta,  d.telefon,  d.ariza,  d.yapilan_islem,  d.sonuc,  d.islemi_yapan_personel, d.aktarilacak_personel,
                d.planlanan_tarih, d.ise_gidecek_persone, d.note, d.olusturma_tarihi, f.firma_ad, s.sube_ad, m.musteri_ad, d.durum
            FROM admind d
            LEFT JOIN firma f ON d.firma_id = f.firma_id
            LEFT JOIN sube s ON d.sube_id = s.sube_id
            LEFT JOIN musteri m ON d.musteri_id = m.musteri_id
            WHERE CAST(d.sonuc AS NVARCHAR(MAX)) <> 'bitti'
            ORDER BY d.olusturma_tarihi DESC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function destek_Bilgi2() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $isim = $_SESSION['isim'] ?? '';

    if (empty($isim)) {
        return [];
    }

    $sql = "SELECT 
        d.destek_id, d.firma_id, d.eposta, d.telefon, d.ariza, d.yapilan_islem, d.sonuc, 
        d.islemi_yapan_personel, d.aktarilacak_personel,
        d.planlanan_tarih, d.ise_gidecek_persone, d.note, d.olusturma_tarihi, 
        f.firma_ad, f.excel_id, s.sube_ad, m.musteri_ad, d.durum
    FROM admind d
    LEFT JOIN firma f ON d.firma_id = f.firma_id
    LEFT JOIN sube s ON d.sube_id = s.sube_id
    LEFT JOIN musteri m ON d.musteri_id = m.musteri_id
    WHERE 
        (d.islemi_yapan_personel LIKE :isim1 
         OR d.aktarilacak_personel LIKE :isim2 
         OR d.ise_gidecek_persone LIKE :isim3)
        AND CAST(d.sonuc AS NVARCHAR(MAX)) <> 'bitti'
    ORDER BY d.olusturma_tarihi DESC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        ':isim1' => "%$isim%", 
        ':isim2' => "%$isim%",
        ':isim3' => "%$isim%"
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
} // <-- Fonksiyon buraya kadar



public function musteriAra($q) {
    $stmt = $this->pdo->prepare("
        SELECT f.firma_id, f.firma_ad, f.excel_id, 
            m.musteri_id, m.musteri_ad, m.musteri_soyad,
            m.telefon, m.email, 
            s.sube_ad, s.sube_id,
            e.envanter_id, e.cihaz_turu, e.marka, e.model, 
            e.islemci, e.bellek, e.disk, e.isletim_sistemi, 
            MAX(CAST(e.uygulamalar AS NVARCHAR(MAX))) AS uygulamalar,
            MAX(CAST(e.bilgi AS NVARCHAR(MAX))) AS bilgi

        FROM musteri m
        LEFT JOIN sube s ON m.sube_id = s.sube_id
        LEFT JOIN firma f ON s.firma_id = f.firma_id
        LEFT JOIN envanter e ON m.musteri_id = e.musteri_id

        WHERE m.musteri_ad LIKE ?
           OR m.musteri_soyad LIKE ?
           OR f.firma_ad LIKE ?
           OR m.telefon LIKE ?
           OR m.email LIKE ?

        GROUP BY 
            f.firma_id, f.firma_ad, f.excel_id, 
            m.musteri_id, m.musteri_ad, m.musteri_soyad,
            m.telefon, m.email, 
            s.sube_ad, s.sube_id,
            e.envanter_id, e.cihaz_turu, e.marka, e.model,
            e.islemci, e.bellek, e.disk, e.isletim_sistemi
    ");

    $like = "%$q%";
    $stmt->execute([$like, $like, $like, $like, $like]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function getFirmaModellerVeDurumById($firma_id){
    $sql = "
        SELECT 
            CASE 
                WHEN EXISTS (
                    SELECT 1
                    FROM abone_hizmet ah
                    INNER JOIN abone_hizmet_hareket ahh ON ah.abone_hizmet_id = ahh.abone_hizmet_id
                    INNER JOIN marka m ON ahh.marka_id = m.marka_id
                    INNER JOIN model m2 ON ahh.model_id = m2.model_id
                    WHERE ah.firma_id = :firma_id1
                      AND m.marka_ad = 'Hizmet'
                       AND (ahh.bitis IS NULL OR ahh.bitis > GETDATE())
                ) THEN 'Aktif'
                ELSE 'Pasif'
            END AS hizmet_durum,
            STUFF((
                SELECT DISTINCT ', ' + m.marka_ad + ' - ' + m2.model_ad
                FROM abone_hizmet ah2
                INNER JOIN abone_hizmet_hareket ahh2 ON ah2.abone_hizmet_id = ahh2.abone_hizmet_id
                INNER JOIN marka m  ON ahh2.marka_id = m.marka_id
                INNER JOIN model m2 ON ahh2.model_id = m2.model_id
                WHERE ah2.firma_id = :firma_id2
                FOR XML PATH(''), TYPE
            ).value('.', 'NVARCHAR(MAX)'), 1, 2, '') AS modeller
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':firma_id1' => $firma_id, ':firma_id2' => $firma_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



    public function kullanicilarBilgi() {
        $sql = "SELECT * FROM kullanici ORDER BY id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAktifKullanicilar() {
        $sql = "SELECT id, isim FROM kullanici WHERE durum = 'aktif' ORDER BY isim ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEnvanterListesi() {
    $sql = "SELECT 
                e.envanter_id, e.musteri_id, e.cihaz_turu, e.marka, e.model, e.islemci,e.bellek,
                e.disk, e.isletim_sistemi, e.uygulamalar, e.bilgi, m.musteri_ad, m.musteri_soyad,
                f.firma_ad, s.sube_ad
            FROM envanter e
            LEFT JOIN musteri m ON m.musteri_id = e.musteri_id
            LEFT JOIN sube s ON s.sube_id = m.sube_id
            LEFT JOIN firma f ON f.firma_id = s.firma_id
            ORDER BY e.envanter_id DESC";

    return $this->pdoQuery($sql); 
}
  
public function addEnvanter($musteri_id, $cihaz_turu, $marka, $model, $islemci, $bellek, $disk, $isletim_sistemi, $uygulamalar, $bilgi) {
        $sql = "INSERT INTO envanter 
                (musteri_id, cihaz_turu, marka, model, islemci, bellek, disk, isletim_sistemi, uygulamalar, bilgi) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        return $this->pdoInsert($sql, [
            $musteri_id, $cihaz_turu, $marka, $model,
            $islemci, $bellek, $disk, $isletim_sistemi,
            $uygulamalar, $bilgi
        ]);
    }

    // ✅ Envanter Güncelle
    public function updateEnvanter($envanter_id, $musteri_id, $cihaz_turu, $marka, $model, $islemci, $bellek, $disk, $isletim_sistemi, $uygulamalar, $bilgi) {
        $sql = "UPDATE envanter SET 
                    musteri_id = ?, 
                    cihaz_turu = ?, 
                    marka = ?, 
                    model = ?, 
                    islemci = ?, 
                    bellek = ?, 
                    disk = ?, 
                    isletim_sistemi = ?, 
                    uygulamalar = ?, 
                    bilgi = ?
                WHERE envanter_id = ?";
        return $this->pdoPrepare($sql, [
            $musteri_id, $cihaz_turu, $marka, $model,
            $islemci, $bellek, $disk, $isletim_sistemi,
            $uygulamalar, $bilgi, $envanter_id
        ]);
    }

    // ✅ Envanter Sil
    public function deleteEnvanter($envanter_id) {
        $sql = "DELETE FROM envanter WHERE envanter_id = ?";
        return $this->pdoDelete($sql, [$envanter_id]);
    }

    // ✅ Tüm Envanterleri Listele (join ile müşteri adı çekilebilir)
    public function getAllEnvanter() {
        $sql = "SELECT e.*, m.musteri_ad, m.musteri_soyad 
                FROM envanter e
                LEFT JOIN musteri m ON e.musteri_id = m.musteri_id
                ORDER BY e.envanter_id DESC";
        return $this->pdoSelect($sql);
    }

    // ✅ Tek bir envanteri getir
    public function getEnvanterById($envanter_id) {
        $sql = "SELECT * FROM envanter WHERE envanter_id = ?";
        return $this->pdoSelect($sql, [$envanter_id], true);
    }
    public function destekEnvanter($musteri_id, $cihaz_turu, $marka, $model, $islemci, $bellek, $disk, $isletim_sistemi, $uygulamalar, $bilgi) {
    $sql = "INSERT INTO envanter 
            (musteri_id, cihaz_turu, marka, model, islemci, bellek, disk, isletim_sistemi, uygulamalar, bilgi) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->pdo->prepare($sql);
    $response = $stmt->execute([
        $musteri_id, 
        $cihaz_turu, 
        $marka, 
        $model, 
        $islemci, 
        $bellek, 
        $disk, 
        $isletim_sistemi, 
        $uygulamalar, 
        $bilgi
    ]);

    if ($response) {
        return true; // başarılı
    } else {
        return false; // başarısız
    }
    }



// Destek Ekle
public function adminEkle(
    $firma_id, $sube_id, $musteri_id, $eposta, $telefon, $ariza,
    $yapilan_islem, $sonuc, $islemi_yapan_personel, $aktarilacak_personel = null,
    $planlanan_tarih = null, $ise_gidecek_persone = null, $note = null,
    $durum = 0 // default değer
) {
    $sql = "INSERT INTO admind 
        (firma_id, sube_id, musteri_id, eposta, telefon, ariza, yapilan_islem, sonuc, 
         islemi_yapan_personel, aktarilacak_personel, planlanan_tarih, ise_gidecek_persone, note, olusturma_tarihi, durum)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $olusturma_tarihi = date('Y-m-d H:i:s');

    $args = [
        $firma_id, $sube_id, $musteri_id, $eposta, $telefon, $ariza,
        $yapilan_islem, $sonuc, $islemi_yapan_personel, $aktarilacak_personel,
        $planlanan_tarih, $ise_gidecek_persone, $note, $olusturma_tarihi,
        $durum
    ];

    return $this->pdoInsert($sql, $args);
}





    

    public function deleteDestek($destek_id) {
    $sql = "DELETE FROM admind WHERE destek_id = ?";
    return $this->pdoDelete($sql, [$destek_id]);
}




   public function updateDestek($data) {
    try {
        $query = "UPDATE destek SET 
            ariza = ?, 
            yapilan_islem = ?, 
            sonuc = ?, 
            islemi_yapan_personel = ?, 
            aktarilacak_personel = ?, 
            planlanan_tarih = ?, 
            ise_gidecek_persone = ?, 
            note = ?
            WHERE destek_id = ?";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $data['ariza'], $data['yapilan_islem'], $data['sonuc'],
            $data['islemi_yapan_personel'], $data['aktarilacak_personel'],
            $data['planlanan_tarih'], $data['ise_gidecek_persone'],
            $data['note'], $data['destek_id']
        ]);
        return true;
    } catch (PDOException $e) {
        // Hata mesajını kaydet veya göster
        return $e->getMessage();
    }
}

    function sadece_admin() {
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || strtolower($_SESSION['rol']) !== 'admin') {
        header("Location: eskidestek.php");
        exit;
    }
}



    public function getEnvanterByMusteriId($musteri_id) {
    $stmt = $this->pdo->prepare("SELECT cihaz_turu, marka, model, islemci, bellek, disk, isletim_sistemi, uygulamalar, bilgi FROM envanter WHERE musteri_id = ?");
    $stmt->execute([$musteri_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



    // Abone Hizmetler + ilk hareket
    public function getAboneHizmetler() {
        $sql = "SELECT 
            ah.abone_hizmet_id,
            ah.aktif,
            f.firma_ad,
            ah.fis_tarih,
            ah.detay,
            ahh.marka_id,
            m.marka_ad,
            ahh.model_id,
            mo.model_ad,
            ahh.aciklama,
            ahh.baslangic,
            ahh.bitis,
            ahh.dongu,
            ahh.miktar,
            ahh.fiyat,
            ahh.tutar,
            ahh.fatura
        FROM abone_hizmet ah
        JOIN firma f ON f.firma_id = ah.firma_id
        LEFT JOIN (
            SELECT *,
                   ROW_NUMBER() OVER(PARTITION BY abone_hizmet_id ORDER BY abone_hizmet_hareket_id ASC) AS rn
            FROM abone_hizmet_hareket
        ) ahh ON ahh.abone_hizmet_id = ah.abone_hizmet_id AND ahh.rn = 1
        LEFT JOIN marka m ON m.marka_id = ahh.marka_id
        LEFT JOIN model mo ON mo.model_id = ahh.model_id
        ORDER BY ah.abone_hizmet_id DESC;
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
// 1. ŞU AN AKTİF OLANLAR (Başlangıcı bugün veya geçmiş, bitişi gelecek olanlar)
public function getAboneHizmetlerAktif() {
    $sql = "SELECT ah.abone_hizmet_id, ah.aktif, f.firma_ad, ah.fis_tarih, ah.detay, 
                   ahh.marka_id, m.marka_ad, ahh.model_id, mo.model_ad, ahh.aciklama, 
                   ahh.baslangic, ahh.bitis, ahh.dongu, ahh.miktar, ahh.fiyat, ahh.tutar, ahh.fatura
            FROM abone_hizmet ah
            JOIN firma f ON f.firma_id = ah.firma_id
            LEFT JOIN (
                SELECT *, ROW_NUMBER() OVER(PARTITION BY abone_hizmet_id ORDER BY abone_hizmet_hareket_id ASC) AS rn
                FROM abone_hizmet_hareket
            ) ahh ON ahh.abone_hizmet_id = ah.abone_hizmet_id AND ahh.rn = 1
            LEFT JOIN marka m ON m.marka_id = ahh.marka_id
            LEFT JOIN model mo ON mo.model_id = ahh.model_id
            WHERE ah.aktif = 1 
              AND ahh.baslangic <= CAST(GETDATE() AS DATE) -- Başlamış olmalı
            ORDER BY ah.abone_hizmet_id DESC";

    return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

// 2. GELECEKTEKİ SÖZLEŞMELER (Başlangıcı bugünden sonra olanlar)
public function getAboneHizmetlerGelecek() {
    $sql = "SELECT ah.abone_hizmet_id, ah.aktif, f.firma_ad, ah.fis_tarih, ah.detay, 
                   ahh.marka_id, m.marka_ad, ahh.model_id, mo.model_ad, ahh.aciklama, 
                   ahh.baslangic, ahh.bitis, ahh.dongu, ahh.miktar, ahh.fiyat, ahh.tutar, ahh.fatura
            FROM abone_hizmet ah
            JOIN firma f ON f.firma_id = ah.firma_id
            LEFT JOIN (
                SELECT *, ROW_NUMBER() OVER(PARTITION BY abone_hizmet_id ORDER BY abone_hizmet_hareket_id ASC) AS rn
                FROM abone_hizmet_hareket
            ) ahh ON ahh.abone_hizmet_id = ah.abone_hizmet_id AND ahh.rn = 1
            LEFT JOIN marka m ON m.marka_id = ahh.marka_id
            LEFT JOIN model mo ON mo.model_id = ahh.model_id
            WHERE ah.aktif = 1 
              AND ahh.baslangic > CAST(GETDATE() AS DATE) -- Henüz başlamamış (Gelecek)
            ORDER BY ahh.baslangic ASC";

    return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}


        // Abone Hizmetler + ilk hareket
    public function getBekleyenTeklifler() {
        $sql = "SELECT 
            ah.teklif_id,
            ah.durum,
            f.firma_ad,
            ah.fis_tarih,
            ah.detay,
            ahh.marka_id,
            m.marka_ad,
            ahh.model_id,
            mo.model_ad,
            ahh.aciklama,
            ahh.baslangic,
            ahh.bitis,
            ahh.dongu,
            ahh.miktar,
            ahh.fiyat,
            ahh.tutar,
            ahh.fatura
        FROM teklif ah
        JOIN firma f ON f.firma_id = ah.firma_id
        LEFT JOIN (
            SELECT *,
                   ROW_NUMBER() OVER(PARTITION BY teklif_id ORDER BY teklif_hareket_id ASC) AS rn
            FROM teklif_hareket
        ) ahh ON ahh.teklif_id = ah.teklif_id AND ahh.rn = 1
        LEFT JOIN marka m ON m.marka_id = ahh.marka_id
        LEFT JOIN model mo ON mo.model_id = ahh.model_id
        WHERE ah.durum NOT IN ('Ret', 'Kabul Edildi')
        ORDER BY ah.teklif_id DESC;
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


        public function getGenelTeklifler() {
        $sql = "SELECT 
            ah.teklif_id,
            ah.durum,
            f.firma_ad,
            ah.fis_tarih,
            ah.detay,
            ahh.marka_id,
            m.marka_ad,
            ahh.model_id,
            mo.model_ad,
            ahh.aciklama,
            ahh.baslangic,
            ahh.bitis,
            ahh.dongu,
            ahh.miktar,
            ahh.fiyat,
            ahh.tutar,
            ahh.fatura
        FROM teklif ah
        JOIN firma f ON f.firma_id = ah.firma_id
        LEFT JOIN (
            SELECT *,
                   ROW_NUMBER() OVER(PARTITION BY teklif_id ORDER BY teklif_hareket_id ASC) AS rn
            FROM teklif_hareket
        ) ahh ON ahh.teklif_id = ah.teklif_id AND ahh.rn = 1
        LEFT JOIN marka m ON m.marka_id = ahh.marka_id
        LEFT JOIN model mo ON mo.model_id = ahh.model_id
        ORDER BY ah.teklif_id DESC;
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAboneHizmetHareketler($abone_hizmet_id) {
    $sql = "SELECT ahh.*, ah.abone_hizmet_id,m.marka_ad, mo.model_ad
            FROM abone_hizmet_hareket ahh
            LEFT JOIN abone_hizmet ah ON ah.abone_hizmet_id = ahh.abone_hizmet_id
            LEFT JOIN marka m ON m.marka_id = ahh.marka_id
            LEFT JOIN model mo ON mo.model_id = ahh.model_id
            WHERE ahh.abone_hizmet_id = ?
            ORDER BY ahh.abone_hizmet_hareket_id ASC";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$abone_hizmet_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}   

    public function getBekleyenTeklifHareketler($teklif_id) {
    $sql = "SELECT ahh.*, m.marka_ad, mo.model_ad
            FROM teklif_hareket ahh
            LEFT JOIN marka m ON m.marka_id = ahh.marka_id
            LEFT JOIN model mo ON mo.model_id = ahh.model_id
            WHERE ahh.teklif_id = ?
            ORDER BY ahh.teklif_hareket_id ASC";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$teklif_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}  

    public function getBitenAboneHizmetlerCustom($baslangic, $bitis) {
    $sql = "SELECT 
                ah.abone_hizmet_id,
                f.firma_ad,
                f.firma_id,
                ah.fis_tarih,
                ahh.marka_id,
                m.marka_ad,
                ahh.model_id,
                mo.model_ad,
                ahh.aciklama,
                ahh.baslangic,
                ahh.bitis,
                ahh.dongu,
                ahh.miktar,
                ahh.fiyat,
                ahh.tutar,
                ahh.fatura
            FROM abone_hizmet ah
            JOIN firma f ON f.firma_id = ah.firma_id
            LEFT JOIN (
                SELECT *,
                       ROW_NUMBER() OVER(PARTITION BY abone_hizmet_id ORDER BY abone_hizmet_hareket_id ASC) AS rn
                FROM abone_hizmet_hareket
            ) ahh ON ahh.abone_hizmet_id = ah.abone_hizmet_id AND ahh.rn = 1
            LEFT JOIN marka m ON m.marka_id = ahh.marka_id
            LEFT JOIN model mo ON mo.model_id = ahh.model_id
            WHERE ahh.bitis BETWEEN ? AND ?            
              AND (ah.durum IS NULL OR ah.durum != 'aktarildi')
            ORDER BY ah.abone_hizmet_id DESC";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$baslangic, $bitis]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



public function getTeklifDetay($teklif_id) {
    $sql = "
        SELECT 
            t.teklif_id,
            t.fis_tarih,
            t.firma_id,
            f.firma_ad,
            f.yetkili,
            f.logo_kod AS firma_logo_kod,  -- firma tablosundan logo kod eklendi
            th.teklif_hareket_id,
            th.aciklama,
            th.tutar,
            th.baslangic,
            th.bitis,
            th.marka_id,
            m.marka_ad,
            th.model_id,
            mo.model_ad,
            mo.logo_kod AS model_logo_kod,
            mo.kdv,
            th.miktar,
            th.fiyat,
            th.dongu,
            th.indirim,
            th.indirim_er,
            th.fatura
        FROM teklif t
        INNER JOIN firma f ON t.firma_id = f.firma_id
        INNER JOIN teklif_hareket th ON t.teklif_id = th.teklif_id
        LEFT JOIN marka m ON th.marka_id = m.marka_id
        LEFT JOIN model mo ON th.model_id = mo.model_id
        WHERE t.teklif_id = ?
        ORDER BY th.teklif_hareket_id ASC
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$teklif_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!$rows) return null;

    $teklif = [
        'teklif_id'  => $rows[0]['teklif_id'],
        'fis_tarih'  => $rows[0]['fis_tarih'],
        'firma_id'   => $rows[0]['firma_id'],
        'firma_ad'   => $rows[0]['firma_ad'],
        'firma_logo_kod' => $rows[0]['firma_logo_kod'], // firma logo kodu
        'yetkili'    => $rows[0]['yetkili'],
        'hareketler' => []
    ];

    foreach($rows as $row) {
        $teklif['hareketler'][] = [
            'teklif_hareket_id' => $row['teklif_hareket_id'],
            'aciklama'          => $row['aciklama'],
            'baslangic'         => $row['baslangic'],
            'bitis'             => $row['bitis'],
            'marka_id'          => $row['marka_id'],
            'marka_ad'          => $row['marka_ad'] ?? 'Bilgi Yok',
            'model_id'          => $row['model_id'],
            'model_ad'          => $row['model_ad'] ?? 'Bilgi Yok',
            'logo_kod'          => $row['model_logo_kod'], // model logo kod
            'miktar'            => $row['miktar'] ?? 0,
            'fiyat'             => $row['fiyat'] ?? 0,
            'indirim'           => $row['indirim'] ?? 0,
            'indirim_er'        => $row['indirim_er'] ?? 0,
            'fatura'            => $row['fatura'] ?? 0,
            'kdv'               => $row['kdv'] ?? 0,
            'dongu'             => $row['dongu'] ?? 0 
        ];
    }

    return $teklif;
}



public function getHizmetDurum($firma_id) {
    $sql = "SELECT 1 
            FROM abone_hizmet ah
            INNER JOIN abone_hizmet_hareket ahh ON ah.abone_hizmet_id = ahh.abone_hizmet_id
            INNER JOIN marka m ON ahh.marka_id = m.marka_id
            WHERE ah.firma_id = :firma_id
              AND m.marka_ad = 'Hizmet'
              AND (ahh.bitis IS NULL OR ahh.bitis > GETDATE())";
    $stmt = $this->pdo->prepare($sql); 
    $stmt->execute([':firma_id' => $firma_id]);
    return $stmt->fetch() ? 'Aktif' : 'Pasif';
}

     public function deleteAboneHizmet($abone_hizmet_id) {
        try {
            $this->pdo->beginTransaction();

            // Bağlı hareketleri sil
            $stmt = $this->pdo->prepare("DELETE FROM abone_hizmet_hareket WHERE abone_hizmet_id = ?");
            $stmt->execute([$abone_hizmet_id]);

            // Master kaydı sil
            $stmt = $this->pdo->prepare("DELETE FROM abone_hizmet WHERE abone_hizmet_id = ?");
            $stmt->execute([$abone_hizmet_id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }


// Excel tablosu için tüm verileri getirir

public function excelOlustur($excelAdi)
{
    try {
        $this->pdo->beginTransaction();

        // 1. Dosyayı oluştur
        $stmt = $this->pdo->prepare("
            INSERT INTO excel_dosyalar (excel_adi, olusturma_tarihi)
            OUTPUT INSERTED.excel_id
            VALUES (?, GETDATE())
        ");
        $stmt->execute([$excelAdi]);
        $excel_id = (int)$stmt->fetchColumn();

        // 2. Dosyaya otomatik ilk sayfayı ekle (Hata almamak için şart)
        $this->sayfaOlustur($excel_id, 'Sayfa1');

        $this->pdo->commit();
        return $excel_id;
    } catch (Exception $e) {
        $this->pdo->rollBack();
        return false;
    }
}


public function getExceller() {
    return $this->pdo
        ->query("SELECT * FROM excel_dosyalar ORDER BY excel_id DESC")
        ->fetchAll(PDO::FETCH_ASSOC);
}
// sayfa
public function sayfaOlustur($excel_id, $sayfaAdi)
{
    $stmt = $this->pdo->prepare("
        INSERT INTO excel_sayfalar (excel_id, sayfa_adi, sira)
        VALUES (
            ?, ?, 
            (SELECT ISNULL(MAX(sira), 0) + 1 FROM excel_sayfalar WHERE excel_id = ?)
        )
    ");

    return $stmt->execute([$excel_id, $sayfaAdi, $excel_id]);
}


public function getSayfalar($excel_id) {
    $stmt = $this->pdo->prepare("
        SELECT * FROM excel_sayfalar
        WHERE excel_id = ?
        ORDER BY sira ASC
    ");
    $stmt->execute([$excel_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function sayfaGuncelle($sayfa_id, $yeniAd) {
    $stmt = $this->pdo->prepare("
        UPDATE excel_sayfalar SET sayfa_adi = ?
        WHERE sayfa_id = ?
    ");
    return $stmt->execute([$yeniAd, $sayfa_id]);
}
// hücre
public function getExcelVerileri($sayfa_id) {
    $stmt = $this->pdo->prepare("
        SELECT hucre_konumu, icerik
        FROM excel_hucreler
        WHERE sayfa_id = ?
    ");
    $stmt->execute([$sayfa_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function saveExcelVerileri($sayfa_id, $payload)
{
    $data = json_decode($payload, true);
    if ($data === null) return "HATA: Gecersiz veri";

    try {
        $this->pdo->beginTransaction();

        // 1. Önce bu sayfadaki tüm eski hücreleri siliyoruz (Hayaletleri temizler)
        $del = $this->pdo->prepare("DELETE FROM excel_hucreler WHERE sayfa_id = ?");
        $del->execute([(int)$sayfa_id]);

        // 2. Eğer veri varsa yeni koordinatlarla ekliyoruz
        if (!empty($data)) {
            $ins = $this->pdo->prepare("INSERT INTO excel_hucreler (sayfa_id, hucre_konumu, icerik) VALUES (?, ?, ?)");
            foreach ($data as $d) {
                if (isset($d['deger']) && trim($d['deger']) !== '') {
                    $ins->execute([
                        (int)$sayfa_id, 
                        $d['hucre'], 
                        trim($d['deger'])
                    ]);
                }
            }
        }

        $this->pdo->commit();
        return "OK";
    } catch (Exception $e) {
        $this->pdo->rollBack();
        return "HATA: " . $e->getMessage();
    }
}


public function getMaxSatir($sayfa_id)
{
    $sql = "
        SELECT 
            ISNULL(MAX(CAST(SUBSTRING(hucre_konumu, 2, LEN(hucre_konumu)) AS INT)), 0)
        FROM excel_hucreler
        WHERE sayfa_id = ?
          AND hucre_konumu LIKE '[A-Z]%'
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$sayfa_id]);
    return (int)$stmt->fetchColumn();
}

public function getMaxSutun($sayfa_id)
{
    $sql = "
        SELECT 
            ISNULL(MAX(ASCII(LEFT(hucre_konumu,1)) - 64), 0) AS max_sutun
        FROM excel_hucreler
        WHERE sayfa_id = ?
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$sayfa_id]);
    return (int)$stmt->fetchColumn();
}
// excel için firma bilkgisi çekme
public function firmaDetay($firma_id)
{
    $stmt = $this->pdo->prepare("SELECT * FROM firma WHERE firma_id = ?");
    $stmt->execute([$firma_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// Firma ile excel bağla
public function firmaExcelBagla($firma_id, $excel_id)
{
    $stmt = $this->pdo->prepare("
        UPDATE firma
        SET excel_id = ?
        WHERE firma_id = ?
    ");

    return $stmt->execute([$excel_id, $firma_id]);
}

public function firmaninExceli($firma_id)
{
    return $this->pdoTek(
        "SELECT excel_id FROM firma WHERE firma_id = ?",
        [$firma_id]
    );
}

public function firmaYetkiliMailGetir($firma_id)
{
    $stmt = $this->pdo->prepare("SELECT yetkili_eposta FROM firma WHERE firma_id = ?");
    $stmt->execute([$firma_id]);
    return $stmt->fetchColumn();
}


}


?>