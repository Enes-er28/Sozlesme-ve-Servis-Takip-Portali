<?php include_once 'template/navbar2.php'; ?>
<?php include_once 'template/sidebar.php'; ?>


<?php
$admin = new AdminClass();
$admin->sadece_admin();
// Firma ve Şube verilerini çek
include_once './data/class.php';
$adminclass = new AdminClass();
$firmalar = $adminclass->firma_Bilgi();
$subeler = $adminclass->getSubeBilgi();

// Şube Ekleme
if (isset($_POST['save']) && $_POST['save'] == 2001) {
    $sube_ad = $adminclass->getSecurity($_POST['sube_ad']);
    $firma_id = intval($_POST['firma_id']);
    $adres = $adminclass->getSecurity($_POST['adres']);
    $telefon = $adminclass->getSecurity($_POST['telefon']);

    $sql = "INSERT INTO sube (sube_ad, firma_id, adres, telefon) VALUES (?, ?, ?, ?)";
    $args = [$sube_ad, $firma_id, $adres, $telefon];
    $adminclass->pdoInsert($sql, $args);
    header("Location: Sube.php");
    exit();
}

// Şube Güncelleme
if (isset($_POST['update']) && $_POST['update'] == 2002) {
    $sube_id = intval($_POST['sube_id']);
    $sube_ad = $adminclass->getSecurity($_POST['sube_ad']);
    $firma_id = intval($_POST['firma_id']);
    $adres = $adminclass->getSecurity($_POST['adres']);
    $telefon = $adminclass->getSecurity($_POST['telefon']);

    $sql = "UPDATE sube SET sube_ad = ?, firma_id = ?, adres = ?, telefon = ? WHERE sube_id = ?";
    $args = [$sube_ad, $firma_id, $adres, $telefon, $sube_id];
    $adminclass->pdoPrepare($sql, $args);
    header("Location: Sube.php"); // İşlem sonrası sayfa yenile
    exit();
}

// Şube Silme
if (isset($_POST['sube_id_delete'])) {
    $delete_id = intval($_POST['sube_id_delete']);
    $sql = "DELETE FROM sube WHERE sube_id = ?";
    $adminclass->pdoDelete($sql, [$delete_id]);
    header("Location: Sube.php"); // İşlem sonrası sayfa yenile
    exit();
}
?>

<div class="content-wrapper">
   <section class="content-header"><h1>Şube Yönetimi</h1></section>
   <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
            <button class="btn btn-success" data-toggle="modal" data-target="#modal-sub-add">Yeni Ekle</button>

        </div>
        <div class="card-body">
          <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                  <th>Şube ID</th>
                  <th>Şube Adı</th>
                  <th>Firma</th>
                  <th>Telefon</th>
                  <th>Adres</th>
                  <th>İşlem</th>
                </tr>
              </thead>
              <tbody>
<?php
$firma_map = [];
foreach ($firmalar as $f) { $firma_map[$f['firma_id']] = $f['firma_ad']; }

foreach ($subeler as $s):
?>
<tr>
  <td><?= $s['sube_id']; ?></td>
  <td><?= htmlspecialchars($s['sube_ad']); ?></td>
  <td><?= $firma_map[$s['firma_id']] ?? 'Bilinmiyor'; ?></td>
  <td><?= htmlspecialchars($s['telefon']); ?></td>
  <td><?= htmlspecialchars($s['adres']); ?></td>
  <td>
    <button class="btn btn-warning" data-toggle="modal" data-target="#modal-edit-<?= $s['sube_id']; ?>">Güncelle</button>
    <button class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-<?= $s['sube_id']; ?>">Sil</button>
  </td>
</tr>

<!-- Güncelle Modal -->
<div class="modal fade" id="modal-edit-<?= $s['sube_id']; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">Şube Güncelle</h4></div>
        <div class="modal-body">
          <div class="form-group">
            <label>Şube Adı</label>
            <input type="text" name="sube_ad" class="form-control" value="<?= $s['sube_ad']; ?>" required>
          </div>
          <div class="form-group">
            <label>Firma</label>
            <select name="firma_id" class="form-control" required>
              <?php foreach ($firmalar as $f): ?>
              <option value="<?= $f['firma_id']; ?>" <?= $f['firma_id'] == $s['firma_id'] ? 'selected' : ''; ?>>
                <?= $f['firma_ad']; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="telefon" class="form-control" value="<?= $s['telefon']; ?>">
          </div>
          <div class="form-group">
            <label>Adres</label>
            <textarea name="adres" class="form-control"><?= $s['adres']; ?></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="sube_id" value="<?= $s['sube_id']; ?>">
          <input type="hidden" name="update" value="2002">
          <button class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Sil Modal -->
<div class="modal fade" id="modal-delete-<?= $s['sube_id']; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">Şube Sil</h4></div>
        <div class="modal-body">
          <p><strong><?= $s['sube_ad']; ?></strong> şubesini silmek istediğinize emin misiniz?</p>
          <input type="hidden" name="sube_id_delete" value="<?= $s['sube_id']; ?>">
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
                <th>Şube ID</th>
                  <th>Şube Adı</th>
                  <th>Firma</th>
                  <th>Telefon</th>
                  <th>Adres</th>
                  <th>İşlem</th>
                    </tr>
                </tfoot>
            </table>

        </div>
      </div>
    </div>
  </section>
</div>

<!-- Yeni Şube Ekle Modal -->
<div class="modal fade" id="modal-sub-add">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">Yeni Şube Ekle</h4></div>
        <div class="modal-body">
          <div class="form-group">
            <label>Şube Adı</label>
            <input type="text" name="sube_ad" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Firma Seç</label>
            <select name="firma_id" class="form-control" required>
              <?php foreach ($firmalar as $f): ?>
              <option value="<?= $f['firma_id']; ?>"><?= $f['firma_ad']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="telefon" class="form-control">
          </div>
          <div class="form-group">
            <label>Adres</label>
            <textarea name="adres" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="save" value="2001">
          <button class="btn btn-success" type="submit">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>
