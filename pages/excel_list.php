<?php
include_once 'template/navbar2.php';
include_once 'template/sidebar.php';

$admin = new AdminClass();
$admin->sadece_admin();

$mesaj = null;

/* === YENİ EXCEL OLUŞTUR (Geliştirilmiş) === */
if (isset($_POST['excel_ekle'])) {
    $excelAdi = urlencode(trim($_POST['excel_adi']) ?: 'Yeni Excel');
    
    // Veritabanına kayıt YAPMIYORUZ. 
    // Doğrudan excel.php'ye "yeni" parametresiyle gidiyoruz.
    header("Location: excel.php?yeni_excel=1&ad=" . $excelAdi);
    exit;
}
/* === EXCEL GÜNCELLE === */
if (isset($_POST['excel_update'])) {

    $excel_id = intval($_POST['excel_id']);
    $excel_adi = trim($_POST['excel_adi']);

    $sql = "UPDATE excel_dosyalar SET excel_adi = ? WHERE excel_id = ?";
    $admin->pdoPrepare($sql, [$excel_adi, $excel_id]);

    header("Location: excel_list.php");
    exit;
}

/* === EXCEL SİL === */
if (isset($_POST['excel_delete'])) {

    $excel_id = intval($_POST['excel_id']);

    $sql = "DELETE FROM excel_dosyalar WHERE excel_id = ?";
    $admin->pdoDelete($sql, [$excel_id]);

    header("Location: excel_list.php");
    exit;
}

/* === TÜM EXCEL’LER === */
$exceller = $admin->getExceller();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Bilgi Tabloları Yönetimi</h1>
    </section>

    <section class="content">
        <div class="container-fluid">

            <!-- EXCEL EKLE -->
            <div class="card col-md-6 mx-auto mb-4">
                <div class="card-header bg-warning">
                    <h3 class="card-title text-black text-center display-6">
                        Bilgi Tabloları
                    </h3>
                </div>

                <div class="card-body text-center">

                    <?= $mesaj ?>

                    <form method="POST" target="_blank">

                        <input type="hidden" name="excel_ekle" value="1">

                        <div class="form-group">
                            <input
                                type="text"
                                name="excel_adi"
                                class="form-control text-center"
                                placeholder="Excel adı (opsiyonel)">
                        </div>

                        <button class="btn btn-success px-5">
                            ➕ Tablo Ekle
                        </button>
                    </form>

                </div>
            </div>

            <!-- EXCEL LİSTESİ -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Kayıtlı Tablolar</h3>
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover text-center mb-0">
                        <thead>
                            <tr>
                                <th>N0</th>
                                <th>Tablo Adı</th>
                                <th>Oluşturma</th>
                                <th>Aç</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($exceller): $i=1; foreach($exceller as $e): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><b><?= htmlspecialchars($e['excel_adi']) ?></b></td>
                                <td><?= date('d.m.Y H:i', strtotime($e['olusturma_tarihi'])) ?></td>
                                <td>
                                    <a href="excel.php?excel_id=<?= $e['excel_id'] ?>" 
                                    target="_blank"
                                    class="btn btn-sm btn-primary">
                                        Aç
                                    </a>
                                    <button class="btn btn-sm btn-warning" 
                                        data-toggle="modal" 
                                        data-target="#modal-edit-<?= $e['excel_id'] ?>">
                                        Güncelle
                                    </button>

                                    <button class="btn btn-sm btn-danger" 
                                        data-toggle="modal" 
                                        data-target="#modal-delete-<?= $e['excel_id'] ?>">
                                        Sil
                                    </button>
                                </td>
                            </tr>

                            <div class="modal fade" id="modal-edit-<?= $e['excel_id'] ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                    <form method="POST">
                                        <div class="modal-header">
                                        <h4 class="modal-title">Tablo Güncelle</h4>
                                        </div>
                                        <div class="modal-body">
                                        <div class="form-group">
                                            <label>Tablo Adı</label>
                                            <input type="text" name="excel_adi" class="form-control"
                                                value="<?= htmlspecialchars($e['excel_adi']) ?>" required>
                                        </div>
                                        </div>
                                        <div class="modal-footer">
                                        <input type="hidden" name="excel_id" value="<?= $e['excel_id'] ?>">
                                        <input type="hidden" name="excel_update" value="1">
                                        <button class="btn btn-primary">Kaydet</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                                </div>

                                <div class="modal fade" id="modal-delete-<?= $e['excel_id'] ?>">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                            <h4 class="modal-title">Tablo Sil</h4>
                                            </div>
                                            <div class="modal-body">
                                            <p>
                                                <b><?= htmlspecialchars($e['excel_adi']) ?></b> silinsin mi?
                                            </p>
                                            </div>
                                            <div class="modal-footer">
                                            <input type="hidden" name="excel_id" value="<?= $e['excel_id'] ?>">
                                            <input type="hidden" name="excel_delete" value="1">
                                            <button class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
                                            <button class="btn btn-danger">Sil</button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>
                                    </div>


                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="4">Henüz Excel oluşturulmamış</td>
                            </tr>
                            
                        <?php endif; ?>
                        </tbody>
                        
                    </table>
                </div>
            </div>
            
        </div>
    </section>
</div>

<div class="modal fade" id="modal-delete-<?= $e['excel_id'] ?>">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">Tablo Sil</h4>
        </div>
        <div class="modal-body">
          <p>
            <b><?= htmlspecialchars($e['excel_adi']) ?></b> silinsin mi?
          </p>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="excel_id" value="<?= $e['excel_id'] ?>">
          <input type="hidden" name="excel_delete" value="1">
          <button class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
          <button class="btn btn-danger">Sil</button>
        </div>
      </form>
    </div>
  </div>
</div>


</body>
</html>
