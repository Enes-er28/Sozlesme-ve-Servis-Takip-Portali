<?php include_once 'template/navbar2.php'; ?>
<?php include_once 'template/sidebar.php'; ?>



<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Marka</h1>
        </div>
      </div>
    </div>
  </section>

<?php
$admin = new AdminClass();
$admin->sadece_admin();
// Ekleme
if (isset($_POST['save']) && $_POST['save'] == 'marka_add') {
    $marka_ad = $adminclass->getSecurity($_POST['marka_ad']);
    $sql = "INSERT INTO marka (marka_ad) VALUES (?)";
    print $adminclass->pdoInsert($sql, [$marka_ad]);
    header("Location: marka.php");
    exit();
}

// Güncelleme
if (isset($_POST['update']) && $_POST['update'] == 'marka_update') {
    $marka_id = $_POST['marka_id'];
    $marka_ad = $_POST['marka_ad'];
    $sql = "UPDATE marka SET marka_ad = ? WHERE marka_id = ?";
    $args = $adminclass->getSecurity([$marka_ad, $marka_id]);
    $result = $adminclass->pdoPrepare($sql, $args);
    echo $result ? '<div class="alert alert-success">Güncelleme Başarılı</div>' : '<div class="alert alert-danger">Güncelleme Başarısız</div>';
    header("Location: marka.php");
    exit();
  }

// Silme
if (isset($_POST['marka_id_delete'])) {
    $marka_id = $_POST['marka_id_delete'];
    $sql = "DELETE FROM marka WHERE marka_id = ?";
    echo $adminclass->pdoDelete($sql, [$marka_id]);
    header("Location: marka.php");
    exit();
  }
?>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add-marka">
          Yeni Ekle
        </button>
      </div>

      <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Marka ID</th>
              <th>Marka Adı</th>
              <th>İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $markalar = $adminclass->getMarkalar();
            foreach ($markalar as $m) { ?>
              <tr>
                <td><?= $m['marka_id'] ?></td>
                <td><?= htmlspecialchars($m['marka_ad']) ?></td>
                <td>
                  <button class="btn btn-warning" data-toggle="modal" data-target="#modal-update<?= $m['marka_id'] ?>">Güncelle</button>
                  <button class="btn btn-danger" data-toggle="modal" data-target="#modal-delete<?= $m['marka_id'] ?>">Sil</button>
                </td>
              </tr>

              <!-- Güncelle Modal -->
              <div class="modal fade" id="modal-update<?= $m['marka_id'] ?>">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST">
                      <div class="modal-header">
                        <h4 class="modal-title">Marka Güncelle</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label>Marka Adı</label>
                          <input type="text" name="marka_ad" class="form-control" value="<?= htmlspecialchars($m['marka_ad']) ?>" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <input type="hidden" name="marka_id" value="<?= $m['marka_id'] ?>">
                        <input type="hidden" name="update" value="marka_update">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Sil Modal -->
              <div class="modal fade" id="modal-delete<?= $m['marka_id'] ?>">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST">
                      <div class="modal-header">
                        <h4 class="modal-title">Marka Sil</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <div class="modal-body">
                        <p><strong>Marka ID:</strong> <?= $m['marka_id'] ?></p>
                        <p><strong>Marka Adı:</strong> <?= htmlspecialchars($m['marka_ad']) ?></p>
                        <input type="hidden" name="marka_id_delete" value="<?= $m['marka_id'] ?>">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
                        <button type="submit" class="btn btn-danger">Sil</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            <?php } ?>
                </tbody>
                <tfoot>
                <tr>
              <th>Marka ID</th>
              <th>Marka Adı</th>
              <th>İşlem</th>
            </tr>
                </tfoot>
            </table>

        </div>
      </div>
    </div>
  </section>
</div>


<!-- Marka Ekle Modal -->
<div class="modal fade" id="modal-add-marka">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">Yeni Marka Ekle</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Marka Adı</label>
            <input type="text" name="marka_ad" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="save" value="marka_add">
          <button type="submit" class="btn btn-primary">Ekle</button>
        </div>
      </form>
    </div>
  </div>
</div>

