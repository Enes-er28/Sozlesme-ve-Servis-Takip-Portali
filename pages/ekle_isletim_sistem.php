<?php include_once 'template/navbar2.php'; ?>
<?php include_once 'template/sidebar.php'; ?>


<?php
$admin = new AdminClass();
$admin->sadece_admin();
// Firma ve Şube verilerini çek
include_once './data/class.php';
$adminclass = new AdminClass();
$isletim_sistemi = $adminclass->getSistemBilgi();

// Şube Ekleme
if (isset($_POST['save']) && $_POST['save'] == 2001) {
    $cihaz_ad = $adminclass->getSecurity($_POST['isletim_sistemi_ad']);

    $sql = "INSERT INTO isletim_sistemi (isletim_sistemi_ad) VALUES (?)";
    $args = [$cihaz_ad];
    $adminclass->pdoInsert($sql, $args);
    header("Location: ekle_isletim_sistem.php");
    exit();
}

// Şube Güncelleme
if (isset($_POST['update']) && $_POST['update'] == 2002) {
    $cihaz_id = intval($_POST['isletim_sistemi_id']);
    $cihaz_ad = $adminclass->getSecurity($_POST['isletim_sistemi_ad']);

    $sql = "UPDATE isletim_sistemi SET isletim_sistemi_ad = ? WHERE isletim_sistemi_id = ?";
    $args = [$cihaz_ad, $cihaz_id];
    $adminclass->pdoPrepare($sql, $args);
    header("Location: ekle_isletim_sistem.php"); // İşlem sonrası sayfa yenile
    exit();
}

// Silme İşlemi
if (isset($_POST['isletim_sistemi_id_delete'])) {
    $delete_id = intval($_POST['isletim_sistemi_id_delete']);
    $sql = "DELETE FROM isletim_sistemi WHERE isletim_sistemi_id = ?";
    $adminclass->pdoDelete($sql, [$delete_id]);
    header("Location: ekle_isletim_sistem.php"); // İşlem sonrası aynı sayfaya dön
    exit();
}

?>

<div class="content-wrapper">
   <section class="content-header"><h1>İşletim Sistemi Türleri</h1></section>
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
                  <th>ID</th>
                  <th>Adı</th>
                  <th>İşlem</th>
                </tr>
              </thead>
              <tbody>
<?php

foreach ($isletim_sistemi as $i):
?>
<tr>
  <td><?= $i['isletim_sistemi_id']; ?></td>
  <td><?= htmlspecialchars($i['isletim_sistemi_ad']); ?></td>
  <td>
    <button class="btn btn-warning" data-toggle="modal" data-target="#modal-edit-<?= $i['isletim_sistemi_id']; ?>">Güncelle</button>
    <button class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-<?= $i['isletim_sistemi_id']; ?>">Sil</button>
  </td>
</tr>

<!-- Güncelle Modal -->
<div class="modal fade" id="modal-edit-<?= $i['isletim_sistemi_id']; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">İşletim Sistemi Güncelle</h4></div>
        <div class="modal-body">
          <div class="form-group">
            <label>Cihaz Adı</label>
            <input type="text" name="isletim_sistemi_ad" class="form-control" value="<?= $i['isletim_sistemi_ad']; ?>" required>
          </div>
        <div class="modal-footer">
          <input type="hidden" name="isletim_sistemi_id" value="<?= $i['isletim_sistemi_id']; ?>">
          <input type="hidden" name="update" value="2002">
          <button class="btn btn-primary">Kaydet</button>
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
                  <th>Adı</th>
                  <th>İşlem</th>
                    </tr>
                </tfoot>
            </table>

        </div>
      </div>
    </div>
  </section>
</div>



<!-- Sil Modal -->
<div class="modal fade" id="modal-delete-<?= $i['isletim_sistemi_id']; ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">İşletim Sistemi Sil</h4>
        </div>
        <div class="modal-body">
          <p><strong><?= htmlspecialchars($i['isletim_sistemi_ad']); ?></strong> cihazını silmek istediğinize emin misiniz?</p>
          <input type="hidden" name="isletim_sistemi_id_delete" value="<?= $i['isletim_sistemi_id']; ?>">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
          <button type="submit" class="btn btn-danger">Sil</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Yeni Şube Ekle Modal -->
<div class="modal fade" id="modal-sub-add">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">Yeni Cihaz Ekle</h4></div>
        <div class="modal-body">
          <div class="form-group">
            <label>Cihaz Adı</label>
            <input type="text" name="isletim_sistemi_ad" class="form-control" required>
          </div>
        <div class="modal-footer">
          <input type="hidden" name="save" value="2001">
          <button class="btn btn-success" type="submit">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>
