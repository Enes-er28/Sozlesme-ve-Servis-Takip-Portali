<?php include_once 'template/navbar2.php'; ?>
<?php include_once 'template/sidebar.php'; ?>

<?php
$admin = new AdminClass();
$admin->sadece_admin();
// Ekleme
if (isset($_POST['save']) && $_POST['save'] == 2001) {
    $rol = $adminclass->getSecurity($_POST['rol']);
    $isim = $adminclass->getSecurity($_POST['isim']);
    $kullanici_adi = $adminclass->getSecurity($_POST['kullanici_adi']);
    $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT); // şifre hashlenmeli
    $durum = $adminclass->getSecurity($_POST['durum']);

    $olusturma_tarihi = date('Y-m-d'); // ✅ Tarihi bugünün tarihi olarak al

    $sql = "INSERT INTO kullanici (rol, isim, kullanici_adi, sifre, durum, olusturma_tarihi) VALUES (?, ?, ?, ?, ?, ?)";
    $args = [$rol, $isim, $kullanici_adi, $sifre, $durum, $olusturma_tarihi];

    print $adminclass->pdoInsert($sql, $args);
    header("Location: kullanicilar.php");
    exit();
}


// Güncelleme
if (isset($_POST['update']) && $_POST['update'] == 2002) {
    $id = intval($_POST['id']);
    $rol = $adminclass->getSecurity($_POST['rol']);
    $isim = $adminclass->getSecurity($_POST['isim']);
    $kullanici_adi = $adminclass->getSecurity($_POST['kullanici_adi']);
    $durum = $adminclass->getSecurity($_POST['durum']);

    // Şifre güncelleme isteğe bağlı, boşsa değiştirme
    if (!empty($_POST['sifre'])) {
        $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
        $sql = "UPDATE kullanici SET rol=?, isim=?, kullanici_adi=?, sifre=?, durum=? WHERE id=?";
        $args = [$rol, $isim, $kullanici_adi, $sifre, $durum, $id];
    } else {
        $sql = "UPDATE kullanici SET rol=?, isim=?, kullanici_adi=?, durum=? WHERE id=?";
        $args = [$rol, $isim, $kullanici_adi, $durum, $id];
    }

    $variable = $adminclass->pdoPrepare($sql, $args);
    if ($variable == 1) {
        print '<div class="alert alert-success">İşlem Başarılı...</div>';
        header("Location: kullanicilar.php");
    exit();
    } else {
        print '<div class="alert alert-danger">İşlem Başarısız...</div>';
    }
    
}

// Silme
if (isset($_POST['id_delete'])) {
    $delete_id = intval($_POST['id_delete']);
    $sql = "DELETE FROM kullanici WHERE id = ?";
    $args = [$delete_id];
    $result = $adminclass->pdoDelete($sql, $args);
    print $result;
    header("Location: kullanicilar.php");
    exit();
}
?>


<div class="content-wrapper">
   <section class="content-header"><h1>Kullanıcılar</h1></section>
   <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
            <button class="btn btn-success" data-toggle="modal" data-target="#modal-add-user">Yeni Ekle</button>
            
        </div>
        <div class="card-body">
          <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                      <th>ID</th>
                      <th>Rol</th>
                      <th>İsim</th>
                      <th>Kullanıcı Adı</th>
                      <th>Şifre</th>
                      <th>Durum</th>
                      <th>Oluşturma Tarihi</th>
                      <th>İşlem</th>
                    </tr>
            </thead>
            <tbody>
                <?php
                $users = $adminclass->kullanicilarBilgi(); // Kullanıcıları çekmek için fonksiyonunu kullan
                if ($users) {
                  foreach ($users as $user) { ?>
                    <tr>
                      <td><?= $user['id'] ?></td>
                      <td><?= htmlspecialchars($user['rol']) ?></td>
                      <td><?= htmlspecialchars($user['isim']) ?></td>
                      <td><?= htmlspecialchars($user['kullanici_adi']) ?></td>
                      <td><?= htmlspecialchars($user['sifre']) ?></td>
                      <td><?= htmlspecialchars($user['durum']) ?></td>
                      <td><?= $user['olusturma_tarihi'] ?></td>
                      <td>
                        <button class="btn btn-warning" data-toggle="modal" data-target="#modal-edit-user<?= $user['id'] ?>">Güncelle</button>
                        <button class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-user<?= $user['id'] ?>">Sil</button>
                      </td>
                    </tr>

                      <!-- Sil Modal -->
                      <div class="modal fade" id="modal-delete-user<?= $user['id'] ?>">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <form method="POST">
                              <div class="modal-header">
                                <h4 class="modal-title">Kullanıcı Sil</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                              </div>
                              <div class="modal-body">
                                <p>Bu kullanıcıyı silmek istediğinize emin misiniz?</p>
                                <p><strong>ID:</strong> <?= $user['id'] ?></p>
                                <p><strong>İsim:</strong> <?= htmlspecialchars($user['isim']) ?></p>
                                <input type="hidden" name="id_delete" value="<?= $user['id'] ?>">
                              </div>
                              <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
                                <button type="submit" class="btn btn-danger">Sil</button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>

                      <!-- Güncelle Modal -->
                      <div class="modal fade" id="modal-edit-user<?= $user['id'] ?>">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <form method="POST">
                              <div class="modal-header">
                                <h4 class="modal-title">Kullanıcı Güncelle</h4>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                              </div>
                              <div class="modal-body">
                                <div class="form-group">
                                  <label>Rol</label>
                                  <select name="rol" class="form-control" required>
                                    <option value="admin" <?= $user['rol']=='Admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="kullanici" <?= $user['rol']=='Kullanici' ? 'selected' : '' ?>>Kullanıcı</option>
                                  </select>
                                </div>
                                <div class="form-group">
                                  <label>İsim</label>
                                  <input type="text" name="isim" class="form-control" value="<?= htmlspecialchars($user['isim']) ?>" required>
                                </div>
                                <div class="form-group">
                                  <label>Kullanıcı Adı</label>
                                  <input type="text" name="kullanici_adi" class="form-control" value="<?= htmlspecialchars($user['kullanici_adi']) ?>" required>
                                </div>
                                <div class="form-group">
                                  <label>Şifre (Değiştirmek için giriniz)</label>
                                  <input type="password" name="sifre" class="form-control" placeholder="Boş bırakılırsa değiştirilmez">
                                </div>
                                <div class="form-group">
                                  <label>Durum</label>
                                  <select name="durum" class="form-control" required>
                                    <option value="aktif" <?= $user['durum']=='aktif' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="pasif" <?= $user['durum']=='pasif' ? 'selected' : '' ?>>Pasif</option>
                                  </select>
                                </div>
                              </div>
                              <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
                                <button type="submit" name="update" value="2002" class="btn btn-primary">Güncelle</button>
                              </div>
                              <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            </form>
                          </div>
                        </div>
                      </div>

                      <?php
                        }
                      }
                      ?>
             </tbody>
            <tfoot>
              <tr>
                <th>ID</th>
                <th>Rol</th>
                <th>İsim</th>
                <th>Kullanıcı Adı</th>
                <th>Şifre</th>
                <th>Durum</th>
                <th>Oluşturma Tarihi</th>
                <th>İşlem</th>
                    </tr>
                </tfoot>
            </table>

        </div>
      </div>
    </div>
  </section>
</div>

<!-- Yeni Kullanıcı Modal -->
<div class="modal fade" id="modal-add-user">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h4 class="modal-title">Yeni Kullanıcı Ekle</h4></div>
        <div class="modal-body">
          <div class="form-group">
            <label>Rol</label>
            <select name="rol" class="form-control" required>
              <option value="">Seçiniz</option>
              <option value="admin">Admin</option>
              <option value="kullanici" selected>Kullanıcı</option>
            </select>
          </div>
          <div class="form-group">
            <label>İsim</label>
            <input type="text" name="isim" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Kullanıcı Adı</label>
            <input type="text" name="kullanici_adi" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Şifre</label>
            <input type="password" name="sifre" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Durum</label>
            <select name="durum" class="form-control" required>
              <option value="aktif" selected>Aktif</option>
              <option value="pasif">Pasif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          <button type="submit" name="save" value="2001" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('#table-users').DataTable({
      dom: 'Bfrtip',
      buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
      paging: true,
      lengthChange: true,
      searching: true,
      ordering: true,
      info: true,
      autoWidth: false,
    });
  });



  $(document).ready(function() {
  var table = $('#kullaniciTablo').DataTable();

  // Rol filtresi
  $('#filterRol').on('change', function() {
    var val = $.fn.dataTable.util.escapeRegex($(this).val());
    table.column(1).search(val ? '^'+val+'$' : '', true, false).draw();
  });

  // Durum filtresi
  $('#filterDurum').on('change', function() {
    var val = $.fn.dataTable.util.escapeRegex($(this).val());
    table.column(5).search(val ? '^'+val+'$' : '', true, false).draw();
  });
});

</script>
