
<?php include_once 'template/navbar.php'; ?>
<style>
.col-ariza { width: 100px; 
}
.col-note { width: 100px; 
}
.col-eposta { width: 200px; 
}
.content-wrapper {
  margin-left: 0 !important;
  padding-left: 15px; /* İstersen */
}
#example1 td {
    white-space: nowrap;       /* Metin alt satıra geçmesin */
    overflow: hidden;          /* Taşan kısmı gizle */
    text-overflow: ellipsis;   /* Üç nokta ile kes */
    max-width: 100px;          /* Maksimum genişlik (px veya %) */
}
.popover {
 /* yatay kaymayı engeller */
    margin: 0 !important;                /* ekstra boşlukları kaldırır */
}
</style>

<?php
require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();
$admin->sadece_admin();
$modalOpen = false;
$foundData = [];
// Örnek firma id


$adminclass = new AdminClass();

$kullanicilar = $adminclass->getAktifKullanicilar();
$envanterler = $admin->getEnvanterListesi(); 
$firmalar = $adminclass->firma_Bilgi();
$subeler = $adminclass->getSubeBilgi();
$musteriler = $adminclass->getMusteriBilgi();



// Eğer destek_id GET ile geldiyse, düzenleme için veriyi al
if (isset($_GET['destek_id']) && !empty($_GET['destek_id'])) {
    $destek_id = (int)$_GET['destek_id'];
    $foundData = $admin->getDestekById($destek_id); // Bu fonksiyonu admin class'a yazman lazım
    $modalOpen = true;
}

// Destek listesi çek
$destekler = $admin->destek_Bilgi();
$destekler = $admin->destek_Bilgi3(); // Destekleri listeleyen fonksiyon
$firmalar = $adminclass->firma_Bilgi();
$subeler = $adminclass->getSubeBilgi();
$musteriler = $adminclass->getMusteriBilgi();

// Destek listesine ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == '15001') {

    // Yukarıdaki gibi POST verilerini al
    $firma_idi   = !empty($_POST['firma_idi']) ? intval($_POST['firma_idi']) : 0;
    $sube_idi    = !empty($_POST['sube_idi']) ? intval($_POST['sube_idi']) : 0;
    $musteri_idi = !empty($_POST['musteri_idi']) ? intval($_POST['musteri_idi']) : 0;
    $epostai = !empty($_POST['epostai']) ? $adminclass->getSecurity($_POST['epostai']) : null;
    $telefoni = !empty($_POST['telefoni']) ? $adminclass->getSecurity($_POST['telefoni']) : null;
    $ariza = !empty($_POST['ariza']) ? $adminclass->getSecurity($_POST['ariza']) : null;
    $yapilan_islem = !empty($_POST['yapilan_islem']) ? $adminclass->getSecurity($_POST['yapilan_islem']) : null;
    $sonuc = !empty($_POST['sonuc']) ? $adminclass->getSecurity($_POST['sonuc']) : null;
    $islemi_yapan_personel = !empty($_POST['islemi_yapan_personel']) ? $adminclass->getSecurity($_POST['islemi_yapan_personel']) : null;
    $note = !empty($_POST['note']) ? $adminclass->getSecurity($_POST['note']) : null;

    // Diğer alanlar için varsayılan boş veya null değerler atayabilirsin
     $islemi_yapan_personel  = !empty($_POST['islemi_yapan_personel'])  ? $adminclass->getSecurity($_POST['islemi_yapan_personel'])  : null;
        $aktarilacak_personel   = !empty($_POST['aktarilacak_personel'])   ? $adminclass->getSecurity($_POST['aktarilacak_personel'])   : null;
        $planlanan_tarih        = !empty($_POST['planlanan_tarih']) ? date('Y-m-d H:i:s', strtotime($_POST['planlanan_tarih']))         : null;
        $ise_gidecek_persone    = !empty($_POST['ise_gidecek_persone'])   ? $adminclass->getSecurity($_POST['ise_gidecek_persone'])     : null;

    $result = $adminclass->adminEkle(
        $firma_idi, $sube_idi, $musteri_idi, $epostai, $telefoni, $ariza,
        $yapilan_islem, $sonuc, $islemi_yapan_personel, $aktarilacak_personel,
        $planlanan_tarih, $ise_gidecek_persone, $note
    );
    header("Location: destek.php");
    exit;
  }

// Müşteri Ekleme ve Envanter Ekleme
$mesaj = ""; // işlem mesajı için

if (isset($_POST['save']) && $_POST['save'] == 3001) {
    // Güvenli veriler
    $ad = $adminclass->getSecurity($_POST['musteri_ad']);
    $soyad = $adminclass->getSecurity($_POST['musteri_soyad']);
    $telefon = $adminclass->getSecurity($_POST['telefon']);
    $email = $adminclass->getSecurity($_POST['email']);
    $sube_id = intval($_POST['sube_id']);

    // Envanter bilgileri
    $cihaz_turu = $adminclass->getSecurity($_POST['cihaz_turu']);
    $marka = $adminclass->getSecurity($_POST['marka']);
    $model = $adminclass->getSecurity($_POST['model']);
    $islemci = $adminclass->getSecurity($_POST['islemci']);
    $bellek = $adminclass->getSecurity($_POST['bellek']);
    $disk = $adminclass->getSecurity($_POST['disk']);
    $isletim_sistemi = $adminclass->getSecurity($_POST['isletim_sistemi']);
    $uygulamalar = $adminclass->getSecurity($_POST['uygulamalar']);
    $bilgi = $adminclass->getSecurity($_POST['bilgi']);

    try {
    // Transaction başlat
    $adminclass->beginTransaction();  // beginTransaction() metodunu AdminClass'a eklemelisin

    // 1) Müşteri ekle
    $sql = "INSERT INTO musteri (musteri_ad, musteri_soyad, telefon, email, sube_id) VALUES (?, ?, ?, ?, ?)";
    $adminclass->pdoInsert($sql, [$ad, $soyad, $telefon, $email, $sube_id]);

    // Son eklenen müşteri ID'sini al
    $musteri_id = $adminclass->lastInsertId(); // lastInsertId metodunu AdminClass'a ekle

    // 2) Envanter ekle (yeni fonksiyon ile)
    $envanterEklendi = $adminclass->destekEnvanter(
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
    );

    if (!$envanterEklendi) {
        throw new Exception("Envanter ekleme başarısız!");
    }

    // Commit işlemi
    $adminclass->commit();

    $mesaj = "Müşteri ve envanter başarıyla eklendi.";
    $musteriler = $adminclass->getMusteriBilgi();
    header("Location: destek.php");
    exit;

} catch (Exception $e) {
    $adminclass->rollBack();
    $mesaj = "Hata oluştu: " . $e->getMessage();
}

}



if (isset($_POST['destek_id_delete'])) {
    $destek_id = intval($_POST['destek_id_delete']);
    $result = $adminclass->deleteDestek($destek_id);

    if ($result) {
        header("Location: destek.php?deleted=1");
        exit;
    } else {
        echo '<div class="alert alert-danger">Silme işlemi başarısız oldu!</div>';
      }

    } elseif (isset($_POST['action']) == '15002') {
        // ✅ EKLEME / GÜNCELLEME işlemi

        $destek_id = !empty($_POST['destek_id']) ? intval($_POST['destek_id']) : null;

        // ID olarak gelen alanlar
        $firma_ida   = !empty($_POST['firma_ida'])   ? intval($_POST['firma_ida'])   : null;
        $sube_ida    = !empty($_POST['sube_ida'])    ? intval($_POST['sube_ida'])    : null;
        $musteri_ida = !empty($_POST['musteri_ida']) ? intval($_POST['musteri_ida']) : null;

        // Diğer alanlar
        $eposta                 = !empty($_POST['eposta'])                 ? $adminclass->getSecurity($_POST['eposta'])                 : null;
        $telefon                = !empty($_POST['telefon'])                ? $adminclass->getSecurity($_POST['telefon'])                : null;
        $ariza                  = !empty($_POST['ariza'])                  ? $adminclass->getSecurity($_POST['ariza'])                  : null;
        $yapilan_islem          = !empty($_POST['yapilan_islem'])          ? $adminclass->getSecurity($_POST['yapilan_islem'])          : null;
        $sonuc                  = !empty($_POST['sonuc'])                  ? $adminclass->getSecurity($_POST['sonuc'])                  : null;
        $islemi_yapan_personel  = !empty($_POST['islemi_yapan_personel'])  ? $adminclass->getSecurity($_POST['islemi_yapan_personel'])  : null;
        $aktarilacak_personel   = !empty($_POST['aktarilacak_personel'])   ? $adminclass->getSecurity($_POST['aktarilacak_personel'])   : null;
        $planlanan_tarih        = !empty($_POST['planlanan_tarih']) ? date('Y-m-d H:i:s', strtotime($_POST['planlanan_tarih']))         : null;
        $ise_gidecek_persone    = !empty($_POST['ise_gidecek_persone'])   ? $adminclass->getSecurity($_POST['ise_gidecek_persone'])     : null;
        $note                   = !empty($_POST['note'])                   ? $adminclass->getSecurity($_POST['note'])                   : null;

        if ($destek_id) {
            // ✅ GÜNCELLEME
            $sql = "UPDATE admind SET 
                ariza = ?, 
                yapilan_islem = ?, 
                sonuc = ?, 
                aktarilacak_personel = ?, 
                planlanan_tarih = ?, 
                ise_gidecek_persone = ?, 
                note = ? 
                WHERE destek_id = ?";

            $args = [
                $ariza,
                $yapilan_islem, $sonuc, $aktarilacak_personel,
                $planlanan_tarih, $ise_gidecek_persone, $note,
                $destek_id
            ];
            $result = $adminclass->pdoPrepare($sql, $args);
            header("Location: destek.php");
    exit;

        }
}

// ✅ Envanter Güncelleme
if (isset($_POST['update']) && $_POST['update'] == 16002) {
    $envanter_id      = intval($_POST['envanter_id']);
    $musteri_id       = intval($_POST['musteri_id']);
    $cihaz_turu       = $adminclass->getSecurity($_POST['cihaz_turu']);
    $marka            = $adminclass->getSecurity($_POST['marka']);
    $model            = $adminclass->getSecurity($_POST['model']);
    $islemci          = $adminclass->getSecurity($_POST['islemci']);
    $bellek           = $adminclass->getSecurity($_POST['bellek']);
    $disk             = $adminclass->getSecurity($_POST['disk']);
    $isletim_sistemi  = $adminclass->getSecurity($_POST['isletim_sistemi']);
    $uygulamalar      = $adminclass->getSecurity($_POST['uygulamalar']);
    $bilgi            = $adminclass->getSecurity($_POST['bilgi']);

    $adminclass->updateEnvanter(
        $envanter_id, $musteri_id, $cihaz_turu, $marka, $model,
        $islemci, $bellek, $disk, $isletim_sistemi,
        $uygulamalar, $bilgi
    );

    header("Location: destek.php?updated=1");
    exit;
}



?>

<div class="content-wrapper">
   <section class="content-header"><h1>Destek</h1></section>
   <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
            <button class="btn btn-success" data-toggle="modal" data-target="#modal-add-e">Yeni Ekle</button>
        </div>
        <div class="card-body">
          <table id="example1" class="table table-bordered table-striped">
                <thead>
                  
                  <tr> 
                    <th>Firma AD</th>
                    <th>Şube AD</th>
                    <th>Müşteri AD</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Arıza</th>
                    <th>Yapılan İşlem</th>
                    <th>Sonuç</th>
                    <th>İşlemi Yapan Personel</th>
                    <th>Oluşturma Tarihi</th>
                    <th>Aktarılacak Personel</th>
                    <th>Planlanan Tarih</th>
                    <th>İşe Gidecek Personel</th>
                    <th>Not</th>                
                    <th>İşlemler</th>
                  </tr>
                </thead>
                <tbody>
                            <?php
                            $destekler = $adminclass->destek_Bilgi3(); // Destek tablosundan veri çekme fonksiyonu örneği
                            if ($destekler) {
    foreach ($destekler as $row) { ?>
       <tr>
            <td><?= htmlspecialchars($row['firma_ad'] ?? 'Bilgi Yok'); ?></td>
            <td><?= htmlspecialchars($row['sube_ad'] ?? 'Bilgi Yok'); ?></td>
            <td><?= htmlspecialchars($row['musteri_ad']); ?></td>
            <td><?= htmlspecialchars($row['eposta']); ?></td>
            <td><?= htmlspecialchars($row['telefon']); ?></td>
            <td>
               <?php if(!empty($row['ariza'])) { ?>
        <span 
            class="popover-cell"
            data-toggle="popover" 
            data-trigger="hover"
            data-html="true"
            data-container="body" 
            data-placement="top"
            data-content="<?= htmlspecialchars($row['ariza']); ?>">
            <?= htmlspecialchars($row['ariza']); ?>
        </span>
    <?php } ?>
            </td>
            <td>
                <?php if(!empty($row['yapilan_islem'])) { ?>
                    <span 
                        class="popover-cell"
                        data-toggle="popover" 
                        data-trigger="hover"
                        data-html="true"
                        data-container="body" 
                        data-placement="top"
                        data-content="<?= htmlspecialchars($row['yapilan_islem']); ?>">
                        <?= htmlspecialchars($row['yapilan_islem']); ?>
                    </span>
                <?php } ?>
            </td>
            <td><?= nl2br(htmlspecialchars($row['sonuc'])); ?></td>
            <td><?= htmlspecialchars($row['islemi_yapan_personel']); ?></td>
            <td><?= date('d.m.Y', strtotime($row['olusturma_tarihi'])); ?></td>
            <td><?= htmlspecialchars($row['aktarilacak_personel']); ?></td>
            <td><?= date('Y-m-d H:i', strtotime($row['planlanan_tarih'])); ?></td>
            <td><?= htmlspecialchars($row['ise_gidecek_persone']); ?></td>
            <td><?php if(!empty($row['note'])) { ?>
        <span 
            class="popover-cell"
            data-toggle="popover" 
            data-trigger="hover"
            data-html="true"
            data-container="body" 
            data-placement="top"
            data-content="<?= htmlspecialchars($row['note']); ?>">
            <?= htmlspecialchars($row['note']); ?>
        </span>
    <?php } ?></td>
            <td>
                <div class="d-flex" style="gap:5px;">
                  
                    <!-- Mevcut Güncelleme butonu -->
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-edit-<?= $row['destek_id']; ?>">
                        Güncelle
                    </button>
                </div>
            </td>
        </tr>
        <!-- Arıza Modal -->
        <div class="modal fade" id="modal-ariza-<?= $row['destek_id']; ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Arıza Detayı</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                <textarea class="form-control" id="modalAriza-<?= $row['destek_id']; ?>"></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary">Kaydet</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Not Modal -->
        <div class="modal fade" id="modal-note-<?= $row['destek_id']; ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Not Detayı</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                <textarea class="form-control" id="modalNote-<?= $row['destek_id']; ?>"></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary">Kaydet</button>
              </div>
            </div>
          </div>
        </div>



<!-- Destek Güncelleme Modal -->
  <div class="modal fade" id="modal-edit-<?= $row['destek_id']; ?>">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="destek.php">
        <div class="modal-header">
          <h4 class="modal-title">Destek Kaydını Güncelle - ID: <?= $row['destek_id']; ?></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="destek_id" value="<?= $row['destek_id']; ?>">
          <div class="container-fluid">
            <div class="row">


                <div class="col-md-6 mb-3">
                  <label>Firma Adı</label>
                  <input type="text" value="<?= htmlspecialchars($row['firma_ad'] ?? ''); ?>" class="form-control" readonly> <!-- name yok -->
                  <input type="hidden" name="firma_ida" value="<?= htmlspecialchars($row['firma_ida'] ?? ''); ?>">
                </div>

                <div class="col-md-6 mb-3">
                  <label>Şube Adı</label>
                  <input type="text" value="<?= htmlspecialchars($row['sube_ad'] ?? ''); ?>" class="form-control" readonly> <!-- name yok -->
                  <input type="hidden" name="sube_ida" value="<?= htmlspecialchars($row['sube_id'] ?? ''); ?>">
                </div>

                <div class="col-md-6 mb-3">
                  <label>Müşteri Adı</label>
                  <input type="text" value="<?= htmlspecialchars($row['musteri_ad'] ?? ''); ?>" class="form-control" readonly> <!-- name yok -->
                  <input type="hidden" name="musteri_ida" value="<?= htmlspecialchars($row['musteri_id'] ?? ''); ?>">
                </div>


              <div class="col-md-6 mb-3">
                <label>E-posta</label>
                <input type="email" name="eposta" value="<?= htmlspecialchars($row['eposta'] ?? ''); ?>" class="form-control" readonly>
              </div>

              <div class="col-md-6 mb-3">
                <label>Telefon</label>
                <input type="text" name="telefon" value="<?= htmlspecialchars($row['telefon'] ?? ''); ?>" class="form-control" readonly >
              </div>

              <div class="col-md-6 mb-3">
                <label>Arıza</label>
                <textarea name="ariza" class="form-control" rows="3"><?= htmlspecialchars($row['ariza'] ?? ''); ?></textarea>
              </div>

              <div class="col-md-6 mb-3">
                <label>Yapılan İşlem</label>
                <textarea name="yapilan_islem" class="form-control" rows="3"><?= htmlspecialchars($row['yapilan_islem'] ?? ''); ?></textarea>
              </div>

              <div class="col-md-6 mb-3">
                <label>Sonuç</label>
                <select name="sonuc" class="form-control" required>
                  <?php 
                    $options = ['bitti', 'devam ediyor', 'aktarıldı', 'yerinde servis'];
                    foreach ($options as $opt) {
                      $selected = ($row['sonuc'] === $opt) ? 'selected' : '';
                      echo "<option value=\"$opt\" $selected>$opt</option>";
                    }
                  ?>
                </select>
              </div>

              <div class="col-md-6 mb-3">
               <?php
              if (session_status() == PHP_SESSION_NONE) {
                  session_start();
              }

              $sessionIsim = $_SESSION['isim'] ?? '';
              ?>

                <div class="form-group">
                  <label>İşlemi Yapan Personel</label>
                  <input type="text" class="form-control" name="islemi_yapan_personel_gorunen" value="<?= htmlspecialchars($sessionIsim); ?>" readonly>
                  <input type="hidden" name="islemi_yapan_personel" value="<?= htmlspecialchars($sessionIsim); ?>">
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <label>Aktarılacak Personel</label>
                <select name="aktarilacak_personel" class="form-control" >
                  <option value="">Seçiniz</option>
                  <?php foreach ($kullanicilar as $k): ?>
                    <option value="<?= htmlspecialchars($k['isim']); ?>" <?= ($row['aktarilacak_personel'] === $k['isim']) ? 'selected' : ''; ?>>
                      <?= htmlspecialchars($k['isim']); ?>
                    </option>
                  <?php endforeach; ?>
                </select></div>

              <div class="col-md-6 mb-3">
                <label>Planlanan Tarih</label>
                <input type="datetime-local" name="planlanan_tarih" 
                       value="<?= !empty($row['planlanan_tarih']) ? date('Y-m-d\TH:i', strtotime($row['planlanan_tarih'])) : ''; ?>" 
                       class="form-control">
              </div>

              <div class="col-md-6 mb-3">
                <label>İşe Gidecek Personel</label>
                <select name="ise_gidecek_persone" class="form-control">
                  <option value="">Seçiniz</option>
                  <?php foreach ($kullanicilar as $k): ?>
                    <option value="<?= htmlspecialchars($k['isim']); ?>" <?= ($row['ise_gidecek_persone'] === $k['isim']) ? 'selected' : ''; ?>>
                      <?= htmlspecialchars($k['isim']); ?>
                    </option>
                  <?php endforeach; ?>
                </select></div>

              <div class="col-12 mb-3">
                <label>Not</label>
                <textarea name="note" class="form-control" rows="3"><?= htmlspecialchars($row['note'] ?? ''); ?></textarea>
              </div>
              
            <div class="form-group">
              <input type="hidden" class="form-control" id="firmaIdInput2" name="firma_gidi" readonly
                  value="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>">   
              <input type="hidden" id="firmaIdHidden" name="firma_gidi" value="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>">
            </div>
          



            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
          <button type="submit" name="action" value="15002" class="btn btn-primary">Güncelle</button>
<!-- Mevcut Lisansları Göster Butonun Yanına Ek -->
<button 
  type="button" 
  id="lisansGosterBtn2"
  class="btn btn-info" 
  data-toggle="modal" 
  data-target="#modal-add-m-l"
>
 Lisansları Göster
</button>


        </div>
      </form>
    </div>
  </div>
</div>






<?php
  }
}

?>

<!-- Destek Ekleme Modal -->
<div class="modal fade" id="modal-add-e">
  <div class="modal-dialog" style="max-width: 1000px; width: 95%;">     
    <div class="modal-content">
      <form method="POST">

        <div class="modal-header">
          <h4 class="modal-title">Destek | Yeni Ekle</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
  
          <div class="form-group">
            <label>Müşteri Ara</label>
            <div style="display:flex; gap:5px;">
              <input type="text" id="musteri_ara_input" class="form-control" placeholder="Ad, Soyad, Telefon veya Email yazın...">
              <button type="button" class="btn btn-info" id="musteri_ara_btn">Ara</button>
            </div>
            <!-- Sonuçları göstermek için -->
            <!-- Arama Sonuçları Kutusu -->
            <div id="musteri_sonuc" style="margin-top:10px; display:none; border:1px solid #ddd; padding:10px; border-radius:5px; max-height: 250px; overflow-y: auto;">

              <strong>Sonuçlar:</strong>
              


              <div id="musteri_bilgileri" style="display: flex; flex-wrap: wrap; gap: 100px; margin-top: 5px;">

                <div style="flex: 1 1 45%; min-width: 150px;">
                  <strong>Firma AD:</strong> <span id="firma_ad_val"></span>
                </div>

                <div style="flex: 1 1 45%; min-width: 150px;">
                  <strong>Şube AD:</strong> <span id="sube_ad_val"></span>
                </div>
               

                <div style="flex: 1 1 45%; min-width: 150px;">
                  <strong>Müşteri AD:</strong> <span id="musteri_ad_val"></span>
                </div>

                <div style="flex: 1 1 45%; min-width: 150px;">
                  <strong>Müşteri SOYAD:</strong> <span id="musteri_soyad_val"></span>
                </div>

                <div style="flex: 1 1 45%; min-width: 150px;">
                  <strong>E-posta:</strong> <span id="eposta_val"></span>
                </div>

                <div style="flex: 1 1 45%; min-width: 150px;">
                  <strong>Telefon:</strong> <span id="telefon_val"></span>
                </div>

                <div style="flex: 1 1 100%; min-width: 150px;">
                  <strong>Envanter Bilgileri:</strong>
                  <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Cihaz Türü:</strong> <span id="cihaz_turu_val"></span></li>
                    <li><strong>Marka:</strong> <span id="marka_val"></span></li>
                    <li><strong>Model:</strong> <span id="model_val"></span></li>
                    <li><strong>İşlemci:</strong> <span id="islemci_val"></span></li>
                    <li><strong>Bellek:</strong> <span id="bellek_val"></span></li>
                    <li><strong>Disk:</strong> <span id="disk_val"></span></li>
                    <li><strong>İşletim Sistemi:</strong> <span id="isletim_sistemi_val"></span></li>
                    <li><strong>Uygulamalar:</strong> <span id="uygulamalar_val"></span></li>
                    <li><strong>Ek Bilgi:</strong> <span id="bilgi_val"></span></li>
                  </ul>
                </div>

                <!-- İstersen ek alanları da buraya ekleyebilirsin -->

              </div>
            </div>
          </div>

          <!-- Gerekli gizli alan: musteri_id -->
         




<div class="form-row d-flex flex-wrap" style="gap:35px;">
    <div class="form-group">
       <input type="hidden" class="form-control" name="musteri_idi" readonly
    value="<?php echo htmlspecialchars($foundData['musteri_id'] ?? ''); ?>">

    </div>
</div>








       <div class="form-row d-flex flex-wrap" style="gap:35px;">
          <div class="form-group">
            <label>Firma Ad</label>
            <input type="text" class="form-control" name="firma_adi" readonly
                value="<?php echo htmlspecialchars($foundData['firma_ad'] ?? ''); ?>">   
             <input type="hidden" name="firma_idi" value="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>">
             </div>




          <div class="form-group">
            <label>Şube AD</label>
            <input type="text" class="form-control" name="sube_adi" readonly
                  value="<?php echo htmlspecialchars($foundData['sube_ad'] ?? ''); ?>">
            <input type="hidden" name="sube_idi" value="<?php echo htmlspecialchars($foundData['sube_id'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label>Cihaz Türü</label>
            <input type="text" class="form-control" name="cihaz_turui" readonly>
          </div>

          <div class="form-group">
            <label>İşletim Sistemi</label>
            <input type="text" class="form-control" name="isletim_sistemii" readonly>
          </div>
        </div>



        <div class="form-row d-flex flex-wrap" style="gap:35px;"> 
          <div class="form-group">
            <label>Müşteri AD</label>
            <input type="text" class="form-control" name="musteri_adi" readonly
                   value="<?php echo htmlspecialchars($foundData['musteri_ad'] ?? ''); ?>">
            </div>

          <div class="form-group">
            <label>Müşteri SOYAD</label>
            <input type="text" class="form-control" name="musteri_soyadi" readonly
                   value="<?php echo htmlspecialchars($foundData['musteri_soyad'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label>Marka</label>
            <input type="text" class="form-control" name="markai" readonly>
          </div>
          <div class="form-group">
            <label>Model</label>
            <input type="text" class="form-control" name="modeli" readonly>
          </div>
        </div>


        <div class="form-row d-flex flex-wrap" style="gap:35px;">  
          <div class="form-group">
            <label>E-posta</label>
            <input type="email" class="form-control" name="epostai" readonly
                   value="<?php echo htmlspecialchars($foundData['eposta'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label>Telefon</label>
            <input type="text" class="form-control" name="telefoni" readonly
                   value="<?php echo htmlspecialchars($foundData['telefon'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label>İşlemci</label>
            <input type="text" class="form-control" name="islemcii" readonly>
          </div>
          <div class="form-group">
            <label>Bellek</label>
            <input type="text" class="form-control" name="belleki" readonly>
          </div>
        </div>


        <div class="form-row">
          <!-- Hizmet Durumu (4'te 1 = 3 kolon) -->
          <div class="form-group col-md-3 mb-3">
            <label>Hizmet Durumu</label>
            <input type="text" class="form-control" name="hizmet_durumi" readonly>
          </div>

          <!-- Disk (4'te 1 = 3 kolon) -->
          <div class="form-group col-md-3 mb-3">
            <label>Disk</label>
            <input type="text" class="form-control" name="diski" readonly>
          </div>

          <!-- Uygulamalar (4'te 2 = 6 kolon) -->
          <div class="form-group col-md-6 mb-3">
            <label>Uygulamalar</label>
            <input type="text" class="form-control" name="uygulamalari" readonly>
          </div>
        </div>



          <div class="d-flex justify-content-between" style="gap:20px; width:100%;">
            <!-- Hizmet Türü -->
            <div class="form-group flex-fill">
              <label>Hizmet Türü</label>
              <input type="text" class="form-control" name="tur_adi" readonly>
            </div>

            <!-- Bilgi -->
            <div class="form-group flex-fill">
              <label>Bilgi</label>
              <input type="text" class="form-control" name="bilgii" readonly>
            </div>
          </div>




          <div class="form-group">
            <label>Arıza</label>
            <textarea class="form-control" name="ariza"><?php echo htmlspecialchars($foundData['ariza'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label>Yapılan İşlem</label>
            <textarea class="form-control" name="yapilan_islem"><?php echo htmlspecialchars($foundData['yapilan_islem'] ?? ''); ?></textarea>
          </div>

          <div class="form-group">
            <label>Sonuç</label>
            <select class="form-control" name="sonuc">
              <?php 
                $options = ['bitti', 'devam ediyor', 'aktarıldı', 'yerinde servis'];
                $current = $foundData['sonuc'] ?? '';
                foreach ($options as $opt) {
                  $selected = ($opt === $current) ? 'selected' : '';
                  echo "<option value=\"$opt\" $selected>$opt</option>";
                }
              ?>
            </select>
          </div>



          <?php
              if (session_status() == PHP_SESSION_NONE) {
                  session_start();
              }

              $sessionIsim = $_SESSION['isim'] ?? '';
              ?>

              <div class="form-group">
                <label>İşlemi Yapan Personel</label>
                <input type="text" class="form-control" name="islemi_yapan_personel_gorunen" value="<?= htmlspecialchars($sessionIsim); ?>" readonly>
                <input type="hidden" name="islemi_yapan_personel" value="<?= htmlspecialchars($sessionIsim); ?>">
              </div>



          <div class="form-group">
            <label>Not</label>
            <textarea class="form-control" name="note"><?php echo htmlspecialchars($foundData['note'] ?? ''); ?></textarea>
          </div>

              <div class="form-group">
                <input type="hidden" class="form-control" id="firmaIdInput" name="firma_aidi" readonly
                    value="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>">   
                <input type="hidden" id="firmaIdHidden" name="firma_aidi" value="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>">
              </div>

                <div class="form-group">
                <input type="hidden" class="form-control" id="musteriIdInput" name="musteri_aidi" readonly
                    value="<?php echo htmlspecialchars($foundData['musteri_id'] ?? ''); ?>">   
                <input type="hidden" id="musteriIdHidden" name="musteri_aidi" value="<?php echo htmlspecialchars($foundData['musteri_id'] ?? ''); ?>">
            </div>
            
            

        <div class="modal-footer justify-content-between">
          <a href="destek.php" class="btn btn-default">Vazgeç</a>
          <div class="form-group">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add-m-e">
              Yeni Müşteri Ekle
            </button>



<button 
  type="button" 
  class="btn btn-primary" 
  data-toggle="modal" 
  data-target="#modal-add-m-l" 
  data-firmaid="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>"
>
  Lisansları Göster
</button>
<button 
  type="button" 
  class="btn btn-warning" 
  id="btnMusteriEnvanterGuncelle" 
  data-toggle="modal" 
  data-target="#modal-edit-musteri"
  data-musteri-id="<?php echo htmlspecialchars($foundData['musteri_id'] ?? ''); ?>"
>
  Müşteri Envanter Güncelle
</button>

          </div>

          <button type="submit" class="btn btn-primary">Yeni Destek Kaydı Oluştur</button>
        </div>

        <input type="hidden" name="action" value='15001'>
        <input type="hidden" name="destek_id" value="<?php echo htmlspecialchars($foundData['destek_id'] ?? ''); ?>">
      </form>
    </div>
  </div>
</div>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Firma AD</th>
                    <th>Şube AD</th>
                    <th>Müşteri AD</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Arıza</th>
                    <th>Yapılan İşlem</th>
                    <th>Sonuç</th>
                    <th>İşlemi Yapan Personel</th>
                    <th>Oluşturma Tarihi</th>
                    <th>Aktarılacak Personel</th>
                    <th>Planlanan Tarih</th>
                    <th>İşe Gidecek Personel</th>
                    <th>Not</th>
                    
                    <th>İşlemler</th>
                  </tr>
                </tfoot>
              </table>
              
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

          <!-- /2. TABLO -->
          <div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">2. Tablo (Filtreleme Paneli)</h3>
  </div>
  <div class="card-body">

    <!-- Bu tablo sadece görsel, filtreleme butonu 1. tabloyu etkiliyor -->
    <table class="table table-bordered" style="width:100%;margin-top:20px;">
      <thead>
        <tr>
          <th>Filtre Paneli</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <!-- Sonuç Dropdown -->
            <select id="filterSonuc" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">Sonuç (Hepsi)</option>
              <option value="bitti">Bitti</option>
              <option value="devam ediyor">Devam Ediyor</option>
              <option value="aktarıldı">Aktarıldı</option>
              <option value="yerinde servis">Yerinde</option>
            </select>

            <!-- İşlemi Yapan -->
            <select id="filterIslemYapan" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">İşlemi Yapan (Hepsi)</option>
              <?php foreach($kullanicilar as $k): ?>
                <option value="<?= htmlspecialchars($k['isim']); ?>"><?= htmlspecialchars($k['isim']); ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Aktarılacak Personel -->
            <select id="filterAktarilacak" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">Aktarılacak (Hepsi)</option>
              <?php foreach($kullanicilar as $k): ?>
                <option value="<?= htmlspecialchars($k['isim']); ?>"><?= htmlspecialchars($k['isim']); ?></option>
              <?php endforeach; ?>
            </select>

            <!-- İşe Gidecek Personel -->
            <select id="filterGidecek" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">Gidecek (Hepsi)</option>
              <?php foreach($kullanicilar as $k): ?>
                <option value="<?= htmlspecialchars($k['isim']); ?>"><?= htmlspecialchars($k['isim']); ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Butonlar -->
            <button id="applyFilters" class="btn btn-primary ml-2">Filtrele</button>
            <button id="clearFilters" class="btn btn-secondary ml-2">Temizle</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>









<!-- Yeni Müşteri Modal -->
<div class="modal fade" id="modal-add-m-e">
  <div class="modal-dialog modal-lg"><!-- geniş modal için -->
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">Yeni Müşteri Ekle</h4>
        </div>
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label>Ad <span class="text-danger">*</span></label>
                <input type="text" name="musteri_ad" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label>Soyad</label>
                <input type="text" name="musteri_soyad" class="form-control">
              </div>

              <div class="col-md-6 mb-3">
                <label>Telefon</label>
                <input type="text" name="telefon" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
              </div>

              <div class="col-md-6 mb-3">
                <label>Firma Seç <span class="text-danger">*</span></label>
                <select name="firma_id" id="firma_select" class="form-control" required>
                  <option value="">Firma Seçiniz</option>
                  <?php foreach ($firmalar as $f): ?>
                    <option value="<?= $f['firma_id']; ?>"><?= htmlspecialchars($f['firma_ad']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label>Şube Seç <span class="text-danger">*</span></label>
                <select name="sube_id" id="sube_select" class="form-control" required>
                  <option value="">Önce firma seçiniz</option>
                  <!-- JS ile dolacak -->
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label>Cihaz Türü <span class="text-danger">*</span></label>
                <input type="text" name="cihaz_turu" value="" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label>Marka</label>
                <input type="text" name="marka" value="" class="form-control">
              </div>

              <div class="col-md-4 mb-3">
                <label>Model</label>
                <input type="text" name="model" value="" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>İşlemci</label>
                <input type="text" name="islemci" value="" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Bellek</label>
                <input type="text" name="bellek" value="" class="form-control">
              </div>

              <div class="col-md-6 mb-3">
                <label>Disk</label>
                <input type="text" name="disk"value="" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label>İşletim Sistemi</label>
                <input type="text" name="isletim_sistemi" value="" class="form-control">
              </div>

              <div class="col-12 mb-3">
                <label>Yüklü Uygulamalar</label>
                <textarea name="uygulamalar" class="form-control" rows="3"></textarea>
              </div>

              <div class="col-12 mb-3">
                <label>Ek Bilgi</label>
                <textarea name="bilgi" class="form-control" rows="3"></textarea>
              </div>
              
           <div class="form-group">
              <input type="hidden" class="form-control" id="firmaIdInput" name="firma_aidi" readonly
                  value="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>">   
              <input type="hidden" id="firmaIdHidden" name="firma_aidi" value="<?php echo htmlspecialchars($foundData['firma_id'] ?? ''); ?>">
            </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
          <button type="submit" name="save" value="3001" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Yodal -->
<div class="modal fade" id="modal-add-m-l" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Firma Lisansları</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table id="lisansTable" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Firma</th>
              <th>Marka</th>
              <th>Model</th>
              <th>Kullanıcı Adı</th>
              <th>Kullanıcı Şifre</th>
              <th>License Key</th>
              <th>Açıklama</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="6" class="text-center">Henüz veri yok</td></tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>


<!-- Envanter güncelleme -->
<div class="modal fade" id="modal-edit-musteri" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form method="POST" id="form-edit-envanter">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Müşteri Envanter Güncelle</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">

        <input type="hidden" name="envanter_id" value="">
          <div class="form-group">
            <label>Firma</label>
            <input type="text" name="firma_ad" class="form-control" readonly>
          </div>
          <div class="form-group">
            <label>Şube</label>
            <input type="text" name="sube_ad" class="form-control" readonly>
          </div>
          <div class="form-group">
            <label>Müşteri</label>
            <input type="text" name="musteri_ad" class="form-control" readonly>
          </div>

          

          <div class="form-group">
            <label>Cihaz Türü</label>
            <input type="text" name="cihaz_turu" class="form-control" >
          </div>
          <div class="form-group">
            <label>Marka</label>
            <input type="text" name="marka" class="form-control">
          </div>
          <div class="form-group">
            <label>Model</label>
            <input type="text" name="model" class="form-control">
          </div>
          <div class="form-group">
            <label>İşlemci</label>
            <input type="text" name="islemci" class="form-control">
          </div>
          <div class="form-group">
            <label>Bellek</label>
            <input type="text" name="bellek" class="form-control">
          </div>
          <div class="form-group">
            <label>Disk</label>
            <input type="text" name="disk" class="form-control">
          </div>
          <div class="form-group">
            <label>İşletim Sistemi</label>
            <input type="text" name="isletim_sistemi" class="form-control">
          </div>
          <div class="form-group">
            <label>Yüklü Uygulamalar</label>
            <textarea name="uygulamalar" class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Ek Bilgi</label>
            <textarea name="bilgi" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
          <button type="submit" name="update" value="16002" class="btn btn-primary">Kaydet</button>
        </div>
      </div>
    </form>
  </div>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
document.getElementById("musteri_ara_btn").addEventListener("click", function () {
    const query = document.getElementById("musteri_ara_input").value.trim();
    if (!query) {
        alert("Lütfen bir arama terimi girin!");
        return;
    }

    fetch("musteri_ara.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            console.log("📥 JSON DATA:", data);

            let sonucDiv = document.getElementById("musteri_sonuc");
            let musteriBilgileriDiv = document.getElementById("musteri_bilgileri");

            if (data.success && data.results.length > 0) {
                let html = `
                <div class="container">
                    <div class="row fw-bold border-bottom py-2">
                        <div class="col">Ad Soyad</div>
                        <div class="col">Firma</div>
                        <div class="col">Şube</div>
                        <div class="col">Tel</div>
                        <div class="col">Email</div>
                        <div class="col">Hizmet</div>
                        <div class="col">Durum</div>
                        <div class="col">İşlem</div>
                    </div>
                `;

                data.results.forEach(musteri => {
                    let musteriJSON = JSON.stringify(musteri).replace(/"/g, '&quot;');
                    html += `
                    <div class="row align-items-center border-bottom py-2">
                        <div class="col">${musteri.musteri_ad} ${musteri.musteri_soyad}</div>
                        <div class="col">${musteri.firma_ad}</div>
                        <div class="col">${musteri.sube_ad}</div>
                        <div class="col">${musteri.telefon}</div>
                        <div class="col">${musteri.email}</div>
                        <div class="col text-truncate">${musteri.tur_adlar ?? '-'}</div>
                        <div class="col">
                            <span class="badge badge-${musteri.hizmet_durum === 'Aktif' ? 'success' : 'secondary'}">
                                ${musteri.hizmet_durum}
                            </span>
                        </div>
                        <div class="col">
                            <button type="button" class="btn btn-sm btn-outline-success musteri-sec-btn" data-json="${musteriJSON}">
                                Bu veriyi kullan
                            </button>
                        </div>
                    </div>
                    `;
                });

                html += `</div>`; // container kapanışı
                musteriBilgileriDiv.innerHTML = html;
                sonucDiv.style.display = "block";
            } else {
                musteriBilgileriDiv.innerHTML = `<p style="color:red;">Müşteri bulunamadı!</p>`;
                sonucDiv.style.display = "block";
            }
        })
        .catch(err => {
            console.error("❌ FETCH HATASI:", err);
            alert("Bir hata oluştu! Konsolu kontrol et.");
        });
});



function musteriSec(musteri) {
  console.log("Seçilen müşteri verisi:", musteri);

  const inputs = {
    firma_adi: document.querySelector("input[name='firma_adi']"),
    firma_idi: document.querySelector("input[name='firma_idi']"),
    firma_aidi: document.querySelector("input[name='firma_aidi']"), 
    sube_adi: document.querySelector("input[name='sube_adi']"),
    sube_idi: document.querySelector("input[name='sube_idi']"),
    musteri_adi: document.querySelector("input[name='musteri_adi']"),
    musteri_soyadi: document.querySelector("input[name='musteri_soyadi']"),
    musteri_aidi: document.querySelector("input[name='musteri_aidi']"),
    musteri_idi: document.querySelector("input[name='musteri_idi']"), 
    epostai: document.querySelector("input[name='epostai']"),
    telefoni: document.querySelector("input[name='telefoni']"),
    cihaz_turui: document.querySelector("input[name='cihaz_turui']"),
    markai: document.querySelector("input[name='markai']"),
    modeli: document.querySelector("input[name='modeli']"),
    islemcii: document.querySelector("input[name='islemcii']"),
    belleki: document.querySelector("input[name='belleki']"),
    diski: document.querySelector("input[name='diski']"),
    isletim_sistemii: document.querySelector("input[name='isletim_sistemii']"),
    uygulamalari: document.querySelector("input[name='uygulamalari']"),
    bilgii: document.querySelector("input[name='bilgii']"),
  };

  console.log("Form inputları:", inputs);

  // Şimdi inputlara veri doldur
  if (inputs.firma_adi) inputs.firma_adi.value = musteri.firma_ad || '';
  if (inputs.firma_idi) inputs.firma_idi.value = musteri.firma_id || '';
  if (inputs.firma_aidi) inputs.firma_aidi.value = musteri.firma_id || '';  // hidden input doldur
  if (inputs.sube_adi) inputs.sube_adi.value = musteri.sube_ad || '';
  if (inputs.sube_idi) inputs.sube_idi.value = musteri.sube_id || '';
  if (inputs.musteri_adi) inputs.musteri_adi.value = musteri.musteri_ad || '';
  if (inputs.musteri_soyadi) inputs.musteri_soyadi.value = musteri.musteri_soyad || '';
  if (inputs.musteri_aidi) inputs.musteri_aidi.value = musteri.musteri_id || ''; // hidden input doldur
  if (inputs.musteri_idi) inputs.musteri_idi.value = musteri.musteri_id || ''; // hidden input doldur
  if (inputs.epostai) inputs.epostai.value = musteri.email || '';
  if (inputs.telefoni) inputs.telefoni.value = musteri.telefon || '';
  if (inputs.cihaz_turui) inputs.cihaz_turui.value = musteri.cihaz_turu || '';
  if (inputs.markai) inputs.markai.value = musteri.marka || '';
  if (inputs.modeli) inputs.modeli.value = musteri.model || '';
  if (inputs.islemcii) inputs.islemcii.value = musteri.islemci || '';
  if (inputs.belleki) inputs.belleki.value = musteri.bellek || '';
  if (inputs.diski) inputs.diski.value = musteri.disk || '';
  if (inputs.isletim_sistemii) inputs.isletim_sistemii.value = musteri.isletim_sistemi || '';
  if (inputs.uygulamalari) inputs.uygulamalari.value = musteri.uygulamalar || '';
  if (inputs.bilgii) inputs.bilgii.value = musteri.bilgi || '';
}

// Buton click eventini dinle
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("musteri-sec-btn")) {
        let musteri = JSON.parse(decodeURIComponent(e.target.dataset.json));

        musteriSec(musteri);
        // Seçim yapıldıktan sonra sonucu gizleyelim
        document.getElementById("musteri_sonuc").style.display = "none";
    }
});










  // PHP'den gelen tüm şubeleri JS objesine aktaralım
  const allSubeler = <?= json_encode($subeler); ?>;

  // Yeni müşteri modalındaki firma seçildiğinde şubeleri filtrele
  document.getElementById('firma_select').addEventListener('change', function() {
    const firmaId = this.value;
    const subeSelect = document.getElementById('sube_select');
    subeSelect.innerHTML = '<option value="">Şube Seçiniz</option>';

    if(firmaId) {
      const filteredSubeler = allSubeler.filter(s => s.firma_id == firmaId);
      filteredSubeler.forEach(sube => {
        const option = document.createElement('option');
        option.value = sube.sube_id;
        option.textContent = sube.sube_ad;
        subeSelect.appendChild(option);
      });
    }
  });

  // Güncelle modalındaki firma seçimi değiştiğinde ilgili şubeleri doldur
  document.querySelectorAll('.firma-select-edit').forEach(firmaSelect => {
    const musteriId = firmaSelect.getAttribute('data-musteri-id');
    const subeSelect = document.querySelector('.sube-select-edit-' + musteriId);

    function doldur(selectedFirmaId, selectedSubeId = null) {
      subeSelect.innerHTML = '';
      if (!selectedFirmaId) {
        subeSelect.innerHTML = '<option value="">Önce firma seçiniz</option>';
        return;
      }
      const filteredSubeler = allSubeler.filter(s => s.firma_id == selectedFirmaId);
      filteredSubeler.forEach(sube => {
        const option = document.createElement('option');
        option.value = sube.sube_id;
        option.textContent = sube.sube_ad;
        if (selectedSubeId && sube.sube_id == selectedSubeId) {
          option.selected = true;
        }
        subeSelect.appendChild(option);
      });
    }

    // İlk yüklemede doğru şubeyi seç
    const başlangıçFirmaId = firmaSelect.value;
    const başlangıçSubeId = <?= json_encode($musteriler); ?>.find(m => m.musteri_id == musteriId).sube_id;
    doldur(başlangıçFirmaId, başlangıçSubeId);

    // Firma seçimi değişince şubeleri güncelle
    firmaSelect.addEventListener('change', function() {
      doldur(this.value);
    });
  });









// Filtreleme Butonu
document.addEventListener("DOMContentLoaded", () => {
  if (!window.jQuery || !$.fn.DataTable) return;

  $('#example1').on('init.dt', function () {
    let dt = $('#example1').DataTable();

    const headers = $("#example1 thead th").map(function(){ 
      return $(this).text().trim().toLowerCase(); 
    }).get();

    const findCol = (...needles) => {
      for (const n of needles) {
        const i = headers.findIndex(h => h.includes(n));
        if (i !== -1) return i;
      }
      return -1;
    };

   // indexleri belirle
let idx = {
  sonuc:  findCol("sonuç","sonuc","durum"),
  islem:  findCol("işlemi yapan","islemi yapan","işlem yapan"),
  aktar:  findCol("aktarılacak","aktarılan","aktarılacak personel","aktarılan personel"),
  gidecek:findCol("gidecek","işe gidecek","ise gidecek")
};

if (idx.sonuc === -1) idx.sonuc = 8;
if (idx.islem === -1) idx.islem = 8;
if (idx.aktar === -1) idx.aktar = 11;
if (idx.gidecek === -1) idx.gidecek = 13;

// test amaçlı konsola yaz
console.log("Bulunan kolonlar:", idx);
console.log("İşlemi Yapan verileri:", dt.column(idx.islem).data().toArray().slice(0, 10));

const norm = v => (v ?? "").toString().trim();

// Filtre uygula
$("#applyFilters").on("click", () => {
  dt.column(idx.sonuc).search(norm($("#filterSonuc").val()), false, true, true);
  dt.column(idx.islem).search(norm($("#filterIslemYapan").val()), false, true, true);
  dt.column(idx.aktar).search(norm($("#filterAktarilacak").val()), false, true, true);
  dt.column(idx.gidecek).search(norm($("#filterGidecek").val()), false, true, true);
  dt.draw();
});

// Filtre temizle
$("#clearFilters").on("click", () => {
  $("#filterSonuc, #filterIslemYapan, #filterAktarilacak, #filterGidecek").val("");
  dt.columns().search("");
  dt.draw();
});
  });
});




document.addEventListener('DOMContentLoaded', function () {
  document.addEventListener('keydown', function (e) {
    // Eğer Enter tuşuna basıldıysa
    if (e.key === 'Enter') {
      const active = document.activeElement;
      
      // Textarea veya button'da değilse (yani input içindeyse) ve modal açıksa
      const isModalOpen = document.querySelector('.modal.show') !== null;

      if (isModalOpen && active.tagName !== 'TEXTAREA' && active.tagName !== 'BUTTON') {
        e.preventDefault();
        e.stopPropagation();
      }
    }
  });
});

// Sayfa yüklendiğinde butona data-firmaid attribute'unu input değerinden koy
$(document).ready(function() {
  var firmaId = $('#firmaIdInput').val();
  $('#lisansGosterBtn').attr('data-firmaid', firmaId);
});

$('#modal-add-m-l').on('show.bs.modal', function (event) {
  // Butondan veri almak yerine inputtan direkt al
  var firmaId = $('#firmaIdInput').val();

  console.log('Inputtan alınan firmaId:', firmaId);

  var modal = $(this);
  var tbody = modal.find('tbody');
  tbody.html('<tr><td colspan="6" class="text-center">Yükleniyor...</td></tr>');

  if (!firmaId) {
    tbody.html('<tr><td colspan="6" class="text-center text-danger">Firma ID bulunamadı.</td></tr>');
    return;
  }

  $.ajax({
    url: '/Erportal/get_lisanslar.php',
    method: 'GET',
    data: { firma_id: firmaId },
    dataType: 'json',
    success: function(response) {
      tbody.empty();

      if (response.success && response.data.length > 0) {
        $.each(response.data, function(i, lisans) {
          tbody.append('<tr>' +
            '<td>' + lisans.firma_ad + '</td>' +
            '<td>' + lisans.marka_ad + '</td>' +
            '<td>' + lisans.model_ad + '</td>' +
            '<td>' + lisans.kullanici_adi + '</td>' +
            '<td>' + lisans.kullanici_sifre + '</td>' +
            '<td>' + lisans.license_key + '</td>' +
            '</tr>');
        });
      } else {
        tbody.html('<tr><td colspan="6" class="text-center">Lisans bulunamadı.</td></tr>');
      }
    },
    error: function(xhr, status, error) {
      tbody.html('<tr><td colspan="6" class="text-center">Veri alınırken hata oluştu.</td></tr>');
      console.error('Ajax hatası:', status, error);
    }
  });
});



  




// Sayfa yüklendiğinde butona data-firmaid attribute'unu input değerinden koy
$(document).ready(function() {
  var firmaId = $('#firmaIdInput2').val();
  $('#lisansGosterBtn').attr('data-firmaid', firmaId);
});

$('#modal-add-m-l').on('show.bs.modal', function (event) {
  // Butondan veri almak yerine inputtan direkt al
  var firmaId = $('#firmaIdInput').val();

  console.log('Inputtan alınan firmaId:', firmaId);

  var modal = $(this);
  var tbody = modal.find('tbody');
  tbody.html('<tr><td colspan="6" class="text-center">Yükleniyor...</td></tr>');

  if (!firmaId) {
    tbody.html('<tr><td colspan="6" class="text-center text-danger">Firma ID bulunamadı.</td></tr>');
    return;
  }

  $.ajax({
    url: '/Erportal/get_lisanslar.php',
    method: 'GET',
    data: { firma_id: firmaId },
    dataType: 'json',
    success: function(response) {
      tbody.empty();

      if (response.success && response.data.length > 0) {
        $.each(response.data, function(i, lisans) {
          tbody.append('<tr>' +
            '<td>' + lisans.firma_ad + '</td>' +
            '<td>' + lisans.marka_ad + '</td>' +
            '<td>' + lisans.model_ad + '</td>' +
            '<td>' + lisans.kullanici_adi + '</td>' +
            '<td>' + lisans.kullanici_sifre + '</td>' +
            '<td>' + lisans.license_key + '</td>' +
            '<td>' + lisans.bilgi + '</td>' +
            '</tr>');
        });
      } else {
        tbody.html('<tr><td colspan="6" class="text-center">Lisans bulunamadı.</td></tr>');
      }
    },
    error: function(xhr, status, error) {
      tbody.html('<tr><td colspan="6" class="text-center">Veri alınırken hata oluştu.</td></tr>');
      console.error('Ajax hatası:', status, error);
    }
  });
});





$('#modal-edit-musteri').on('show.bs.modal', function (event) {
  var modal = $(this);
  var musteriId = $('#musteriIdInput').val();

  if (!musteriId) {
    alert('Müşteri ID bulunamadı.');
    modal.modal('hide');
    return;
  }

  // Modaldaki form inputlarını temizle
  modal.find('input, textarea, select').val('');

  // Ajax ile veri çek
  $.ajax({
    url: '/Erportal/get_envanter.php',
    method: 'GET',
    data: { musteri_id: musteriId },
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        var data = response.data;

        // Örnek inputlar
        modal.find('input[name="envanter_id"]').val(data.envanter_id);
        modal.find('input[name="firma_ad"]').val(data.firma_ad);
        modal.find('input[name="sube_ad"]').val(data.sube_ad);
        modal.find('input[name="musteri_ad"]').val(data.musteri_ad);   
        modal.find('input[name="cihaz_turu"]').val(data.cihaz_turu);
        modal.find('input[name="marka"]').val(data.marka);
        modal.find('input[name="model"]').val(data.model);
        modal.find('input[name="islemci"]').val(data.islemci);
        modal.find('input[name="bellek"]').val(data.bellek);
        modal.find('input[name="disk"]').val(data.disk);
        modal.find('input[name="isletim_sistemi"]').val(data.isletim_sistemi);
        modal.find('textarea[name="uygulamalar"]').val(data.uygulamalar);
        modal.find('textarea[name="bilgi"]').val(data.bilgi);

        // Firma, şube, müşteri bilgilerini göstermek istersen:
        modal.find('#firma_ad_display').text(data.firma_ad);
        modal.find('#sube_ad_display').text(data.sube_ad);
        modal.find('#musteri_ad_display').text(data.musteri_ad + ' ' + data.musteri_soyad);
      } else {
        alert('Envanter bulunamadı.');
        modal.modal('hide');
      }
    },
    error: function() {
      alert('Envanter bilgileri yüklenirken hata oluştu.');
      modal.modal('hide');
    }
  });
});


let idleTime = 0;

// Kullanıcı etkinliği olursa süreyi sıfırla
function resetIdleTime() {
    idleTime = 0;
}

// Her 1 saniyede idleTime'ı artır
setInterval(() => {
    idleTime++;
    if (idleTime >= 120) { // 120 saniye = 2 dakika
        location.reload(); // Sayfayı yenile
    }
}, 1000);

// Olayları dinle
document.addEventListener("mousemove", resetIdleTime);
document.addEventListener("keypress", resetIdleTime);
document.addEventListener("click", resetIdleTime);

$(document).ready(function(){
    $('.btn-ariza').on('click', function(){
        var destekId = $(this).data('target').replace('#modal-ariza-', '');
        var ariza = $(this).data('ariza') || '';
        $('#modalAriza-' + destekId).val(ariza);
    });

    $('.btn-note').on('click', function(){
        var destekId = $(this).data('target').replace('#modal-note-', '');
        var note = $(this).data('note') || '';
        $('#modalNote-' + destekId).val(note);
    });
});




$(document).ready(function(){
    $('.popover-cell').popover({
        trigger: 'hover',
        html: true,
        placement: 'auto',   // otomatik en uygun pozisyon
        boundary: 'viewport' // ekran sınırlarına göre konumlandır
    });
});




</script>


</body>
</html>   