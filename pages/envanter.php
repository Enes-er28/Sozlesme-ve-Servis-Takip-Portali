<?php include_once 'template/navbar.php'; ?>
<style>

.content-wrapper {
  margin-left: 0 !important;
  padding-left: 15px; /* İstersen */
}

</style>


<?php if (!empty($mesaj)): ?>

  <div class="alert alert-info"><?= $mesaj; ?></div>
<?php endif; ?>

<?php


// AdminClass dahil ve örneği
// require_once 'AdminClass.php';  // Dosyanın yoluna göre ekle
require_once __DIR__ . '/../data/class.php';
$adminclass = new AdminClass();
$admin = new AdminClass();
$envanterler = $admin->getEnvanterListesi(); 
$firmalar = $adminclass->firma_Bilgi();
$subeler = $adminclass->getSubeBilgi();
$musteriler = $adminclass->getMusteriBilgi();
// Cihaz Türleri
$cihazTurleri = $adminclass->pdoQuery("SELECT cihaz_id, cihaz_ad FROM cihaz_turu");

// İşletim Sistemleri
$isletimSistemleri = $adminclass->pdoQuery("SELECT isletim_sistemi_id, isletim_sistemi_ad FROM isletim_sistemi");





// ✅ Envanter Ekleme
if (isset($_POST['save_envanter']) && $_POST['save_envanter'] == 16001) {
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

    $mesaj = $adminclass->addEnvanter(
        $musteri_id, $cihaz_turu, $marka, $model,
        $islemci, $bellek, $disk, $isletim_sistemi,
        $uygulamalar, $bilgi
    );

    header("Location: envanter.php?added=1");
    exit;
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

    header("Location: envanter.php?updated=1");
    exit;
}

// ✅ Envanter Silme
if (isset($_POST['envanter_id_delete'])) {
    $delete_id = intval($_POST['envanter_id_delete']);
    $adminclass->deleteEnvanter($delete_id);

    header("Location: envanter.php?deleted=1");
    exit;
}
?>

<div class="content-wrapper">
   <section class="content-header"><h1>Envanter</h1></section>
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
                        <th>ID</th>
                        <th>Firma</th>
                        <th>Şube</th>
                        <th>Müşteri</th>
                        <th>Cihaz Türü</th>
                        <th>Marka</th>
                        <th>Model</th>
                        <th>İşlemci</th>
                        <th>Bellek</th>
                        <th>Disk</th>
                        <th>İşletim Sistemi</th>
                        <th>Uygulamalar</th>
                        <th>Bilgi</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($envanterler as $e): ?>
                        <tr>
                            <td><?= $e['envanter_id']; ?></td>
                            <td><?= htmlspecialchars($e['firma_ad'] ?? 'Belirtilmemiş'); ?></td>
                            <td><?= htmlspecialchars($e['sube_ad'] ?? 'Belirtilmemiş'); ?></td>
                            <td><?= htmlspecialchars($e['musteri_ad'] . ' ' . $e['musteri_soyad']); ?></td>
                            <td><?= htmlspecialchars($e['cihaz_turu']); ?></td>
                            <td><?= htmlspecialchars($e['marka']); ?></td>
                            <td><?= htmlspecialchars($e['model']); ?></td>
                            <td><?= htmlspecialchars($e['islemci']); ?></td>
                            <td><?= htmlspecialchars($e['bellek']); ?></td>
                            <td><?= htmlspecialchars($e['disk']); ?></td>
                            <td><?= htmlspecialchars($e['isletim_sistemi']); ?></td>
                            <td><?= nl2br(htmlspecialchars($e['uygulamalar'])); ?></td>
                            <td><?= nl2br(htmlspecialchars($e['bilgi'])); ?></td>
                            <td>
                                <!-- Güncelle butonu -->
                                <button class="btn btn-warning btn-sm" 
                                        data-toggle="modal" 
                                        data-target="#modal-edit-<?= $e['envanter_id']; ?>">
                                    Güncelle
                                </button>

                                <!-- Sil butonu -->
                                <form method="POST" style="display:inline;" 
                                    onsubmit="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="envanter_id_delete" value="<?= $e['envanter_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                </form>
                            </td>
                        </tr>
                        
 <!-- Güncelle Modal -->
<div class="modal fade" id="modal-edit-<?= $e['envanter_id']; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">Envanter Güncelle</h4></div>
        <div class="modal-body">

<!-- Firma Seç -->
<div class="form-group">
  <label>Firma Seç</label>
  <select name="firma_id" 
          class="form-control firma-select-edit" 
          id="firma_select_<?= $e['envanter_id']; ?>" 
          data-envanter-id="<?= $e['envanter_id']; ?>"
          data-mevcut-firma-id="<?= htmlspecialchars($e['firma_id'] ?? '') ?>" 
          required>
    <option value="">Firma Seçiniz</option>
    <?php foreach ($firmalar as $f): ?>
      <option value="<?= $f['firma_id']; ?>" <?= (($e['firma_id'] ?? null) == $f['firma_id']) ? 'selected' : ''; ?>>
        <?= htmlspecialchars($f['firma_ad']); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Şube Seç -->
<div class="form-group">
  <label>Şube Seç</label>
  <select name="sube_id" 
          class="form-control sube-select-edit" 
          id="sube_select_<?= $e['envanter_id']; ?>" 
          data-envanter-id="<?= $e['envanter_id']; ?>"
          data-mevcut-sube-id="<?= htmlspecialchars($e['sube_id'] ?? '') ?>" 
          required>
    <option value="">Önce firma seçiniz</option>
  </select>
</div>

<!-- Müşteri Seç -->
<div class="form-group">
  <label>Müşteri Seç</label>
  <select name="musteri_id" 
          class="form-control musteri-select-edit" 
          id="musteri_select_<?= $e['envanter_id']; ?>" 
          data-envanter-id="<?= $e['envanter_id']; ?>"
          data-mevcut-musteri-id="<?= htmlspecialchars($e['musteri_id'] ?? '') ?>" 
          required>
    <option value="">Önce şube seçiniz</option>
  </select>
</div>


<div class="form-group">
  <label>Cihaz Türü</label>
  <select name="cihaz_turu" class="form-control" id="cihaz_turu_select">
    <option value="">Seçiniz</option>
    <?php foreach ($cihazTurleri as $ct): ?>
      <option value="<?= htmlspecialchars($ct['cihaz_ad']); ?>" 
        <?= (($e['cihaz_turu'] ?? null) == $ct['cihaz_ad']) ? 'selected' : ''; ?>>
        <?= htmlspecialchars($ct['cihaz_ad']); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>


          <div class="form-group">
            <label>Marka</label>
            <input type="text" name="marka" value="<?= htmlspecialchars($e['marka']); ?>" class="form-control">
          </div>

          <div class="form-group">
            <label>Model</label>
            <input type="text" name="model" value="<?= htmlspecialchars($e['model']); ?>" class="form-control">
          </div>

          <div class="form-group">
            <label>İşlemci</label>
            <input type="text" name="islemci" value="<?= htmlspecialchars($e['islemci']); ?>" class="form-control">
          </div>

          <div class="form-group">
            <label>Bellek</label>
            <input type="text" name="bellek" value="<?= htmlspecialchars($e['bellek']); ?>" class="form-control">
          </div>

          <div class="form-group">
            <label>Disk</label>
            <input type="text" name="disk" value="<?= htmlspecialchars($e['disk']); ?>" class="form-control">
          </div>
          
<div class="form-group">
  <label>İşletim Sistemi</label>
  <select name="isletim_sistemi" class="form-control">
    <option value="">Seçiniz</option>
    <?php foreach ($isletimSistemleri as $os): ?>
      <option value="<?= htmlspecialchars($os['isletim_sistemi_ad']); ?>" 
        <?= (($e['isletim_sistemi'] ?? null) == $os['isletim_sistemi_ad']) ? 'selected' : ''; ?>>
        <?= htmlspecialchars($os['isletim_sistemi_ad']); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>


          <div class="form-group">
            <label>Yüklü Uygulamalar</label>
            <textarea name="uygulamalar" class="form-control"><?= htmlspecialchars($e['uygulamalar']); ?></textarea>
          </div>

          <div class="form-group">
            <label>Ek Bilgi</label>
            <textarea name="bilgi" class="form-control"><?= htmlspecialchars($e['bilgi']); ?></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <input type="hidden" name="envanter_id" value="<?= $e['envanter_id']; ?>">
          <input type="hidden" name="update" value="16002">
          <button class="btn btn-primary" type="submit">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>



        <!-- Sil Modal -->
            <div class="modal fade" id="modal-delete-<?= $e['envanter_id']; ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                <form method="POST">
                    <div class="modal-header"><h4 class="modal-title">Envanteri Sil</h4></div>
                    <div class="modal-body">
                    <p><strong><?= htmlspecialchars($e['cihaz_turu'] . ' ' . $e['marka'] . ' ' . $e['model']); ?></strong> kaydını silmek istediğinize emin misiniz?</p>
                    <input type="hidden" name="envanter_id_delete" value="<?= $e['envanter_id']; ?>">
                    </div>
                    <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
                    <button class="btn btn-danger" type="submit">Sil</button>
                    </div>
                </form>
                </div>
            </div>
            </div>
            
            <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Firma</th>
                        <th>Şube</th>
                        <th>Müşteri</th>
                        <th>Cihaz Türü</th>
                        <th>Marka</th>
                        <th>Model</th>
                        <th>İşlemci</th>
                        <th>Bellek</th>
                        <th>Disk</th>
                        <th>İşletim Sistemi</th>
                        <th>Uygulamalar</th>
                        <th>Bilgi</th>
                        <th>İşlem</th>
                    </tr>
                </tfoot>
            </table>

        </div>
      </div>
    </div>
  </section>
</div>


          


<!-- Yeni Envanter Modal -->
<div class="modal fade" id="modal-add-e">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">Yeni Envanter Ekle</h4>
        </div>
        <div class="modal-body">

          <div class="form-group">
            <label>Firma Seç</label>
            <select name="firma_id" id="firma_select" class="form-control" required>
              <option value="">Firma Seçiniz</option>
              <?php foreach ($firmalar as $f): ?>
                <option value="<?= $f['firma_id']; ?>">
                  <?= htmlspecialchars($f['firma_ad']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Şube Seç -->
          <div class="form-group">
            <label>Şube Seç</label>
            <select name="sube_id" id="sube_select" class="form-control" required>
              <option value="">Önce firma seçiniz</option>
            </select>
          </div>

          <!-- Müşteri Seç -->
          <div class="form-group">
            <label>Müşteri Seç</label>
            <select name="musteri_id" id="musteri_select" class="form-control" required>
              <option value="">Önce şube seçiniz</option>
            </select>
          </div>

          <!-- Cihaz Bilgileri -->
          <div class="form-group">
  <label>Cihaz Türü</label>
  <select name="cihaz_turu" class="form-control" id="cihaz_turu_select">
    <option value="">Seçiniz</option>
    <?php foreach ($cihazTurleri as $ct): ?>
      <option value="<?= $ct['cihaz_ad']; ?>"><?= htmlspecialchars($ct['cihaz_ad']); ?></option>
    <?php endforeach; ?>
  </select>
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
          </div>,
        <div class="form-group">
  <label>İşletim Sistemi</label>
  <select name="isletim_sistemi" class="form-control" >
    <option value="">Seçiniz</option>
    <?php foreach ($isletimSistemleri as $os): ?>
      <option value="<?= $os['isletim_sistemi_ad']; ?>" >
        <?= htmlspecialchars($os['isletim_sistemi_ad']); ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>
          <div class="form-group">
            <label>Uygulamalar</label>
            <textarea name="uygulamalar" class="form-control"></textarea>
          </div>
          <div class="form-group">
            <label>Bilgi</label>
            <textarea name="bilgi" class="form-control"></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
          <button type="submit" name="save_envanter" value="16001" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
  const allSubeler   = <?= json_encode($subeler); ?>;    // [{sube_id, firma_id, sube_ad}, ...]
  const allMusteriler = <?= json_encode($musteriler); ?>; // [{musteri_id, sube_id, musteri_ad, musteri_soyad}, ...]
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const allSubeler = <?= json_encode($subeler); ?>;
  const allMusteriler = <?= json_encode($musteriler); ?>;

  // --- GÜNCELLEME MODALLARI İÇİN ---

  // Her "firma-select-edit" sınıfına sahip select için işlemleri kur
  document.querySelectorAll(".firma-select-edit").forEach(firmaSelect => {
    const envanterId = firmaSelect.dataset.envanterId;
    const subeSelect = document.getElementById("sube_select_" + envanterId);
    const musteriSelect = document.getElementById("musteri_select_" + envanterId);

    if (!subeSelect || !musteriSelect) return;

    function doldurSubeler(firmaId) {
      subeSelect.innerHTML = '<option value="">Şube Seçiniz</option>';
      musteriSelect.innerHTML = '<option value="">Müşteri Seçiniz</option>';
      if (!firmaId) return;

      allSubeler.filter(s => String(s.firma_id) === String(firmaId))
        .forEach(sube => {
          const opt = document.createElement('option');
          opt.value = sube.sube_id;
          opt.textContent = sube.sube_ad;
          subeSelect.appendChild(opt);
        });
    }

    function doldurMusteriler(subeId) {
      musteriSelect.innerHTML = '<option value="">Müşteri Seçiniz</option>';
      if (!subeId) return;

      allMusteriler.filter(m => String(m.sube_id) === String(subeId))
        .forEach(musteri => {
          const opt = document.createElement('option');
          opt.value = musteri.musteri_id;
          opt.textContent = musteri.musteri_ad + " " + musteri.musteri_soyad;
          musteriSelect.appendChild(opt);
        });
    }

    firmaSelect.addEventListener("change", function() {
      doldurSubeler(this.value);
    });

    subeSelect.addEventListener("change", function() {
      doldurMusteriler(this.value);
    });

    // Eğer güncelleme modalı açılırken seçili değerleri doldurmak istersen,
    // bunları data attribute olarak set edip burada alıp seçebilirsin.
    const mevcutFirmaId = firmaSelect.getAttribute("data-mevcut-firma-id");
    const mevcutSubeId = subeSelect.getAttribute("data-mevcut-sube-id");
    const mevcutMusteriId = musteriSelect.getAttribute("data-mevcut-musteri-id");

    if (mevcutFirmaId) {
      firmaSelect.value = mevcutFirmaId;
      doldurSubeler(mevcutFirmaId);

      setTimeout(() => {
        if (mevcutSubeId) {
          subeSelect.value = mevcutSubeId;
          doldurMusteriler(mevcutSubeId);

          setTimeout(() => {
            if (mevcutMusteriId) {
              musteriSelect.value = mevcutMusteriId;
            }
          }, 100);
        }
      }, 100);
    }
  });


  // --- YENİ EKLEME MODALI İÇİN ---

  const firmaSelectAdd = document.getElementById("firma_select");
  const subeSelectAdd = document.getElementById("sube_select");
  const musteriSelectAdd = document.getElementById("musteri_select");

  if (firmaSelectAdd && subeSelectAdd && musteriSelectAdd) {
    firmaSelectAdd.addEventListener("change", function() {
      const firmaId = this.value;
      subeSelectAdd.innerHTML = '<option value="">Şube Seçiniz</option>';
      musteriSelectAdd.innerHTML = '<option value="">Önce şube seçiniz</option>';

      if (!firmaId) return;

      allSubeler.filter(s => String(s.firma_id) === String(firmaId))
        .forEach(sube => {
          const opt = document.createElement('option');
          opt.value = sube.sube_id;
          opt.textContent = sube.sube_ad;
          subeSelectAdd.appendChild(opt);
        });
    });

    subeSelectAdd.addEventListener("change", function() {
      const subeId = this.value;
      musteriSelectAdd.innerHTML = '<option value="">Müşteri Seçiniz</option>';

      if (!subeId) return;

      allMusteriler.filter(m => String(m.sube_id) === String(subeId))
        .forEach(musteri => {
          const opt = document.createElement('option');
          opt.value = musteri.musteri_id;
          opt.textContent = musteri.musteri_ad + " " + musteri.musteri_soyad;
          musteriSelectAdd.appendChild(opt);
        });
    });
  }

});




</script>
