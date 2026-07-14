<?php include_once 'template/navbar2.php'; ?>
<?php include_once 'template/sidebar.php'; ?>


<?php
$admin = new AdminClass();
$admin->sadece_admin();
// Veriler (örnek metodlar, kendi adminclass’ını kullanabilirsin)
$markalar = $adminclass->getMarkalar();   // Tüm markalar: [ ['marka_id'=>1, 'marka_ad'=>'Marka1'], ... ]
$modeller = $adminclass->getModeller();   // Tüm modeller: [ ['model_id'=>1, 'model_ad'=>'Model1', 'marka_id'=>1, 'marka_ad'=>'Marka1'], ... ]

// Ekleme işlemi
if (isset($_POST['save']) && $_POST['save'] == 3001) {
    $model_ad = $adminclass->getSecurity($_POST['model_ad']);
    $marka_id = intval($_POST['marka_id']);
    $logo_kod = $adminclass->getSecurity($_POST['logo_kod']);
    $model_fiyat = intval($_POST['model_fiyat']);
    $kdv = intval($_POST['kdv']);
    $sql = "INSERT INTO model (model_ad, marka_id, logo_kod, model_fiyat, kdv) VALUES (?, ?, ?, ?, ?)";
    $adminclass->pdoInsert($sql, [$model_ad, $marka_id, $logo_kod, $model_fiyat, $kdv]);
    header("Location: model.php");
    exit();
}

// Güncelleme işlemi
if (isset($_POST['update']) && $_POST['update'] == 3002) {
    $model_id = intval($_POST['model_id']);
    $model_ad = $adminclass->getSecurity($_POST['model_ad']);
    $marka_id = intval($_POST['marka_id']);
    $logo_kod = $adminclass->getSecurity($_POST['logo_kod']);
    $model_fiyat = intval($_POST['model_fiyat']);
    $kdv = intval($_POST['kdv']);
    $sql = "UPDATE model SET model_ad = ?, marka_id = ?, logo_kod = ?, model_fiyat = ?, kdv = ? WHERE model_id = ?";
    $adminclass->pdoPrepare($sql, [$model_ad, $marka_id, $logo_kod, $model_fiyat, $kdv, $model_id]);
    header("Location: model.php");
    exit();
}

// Silme işlemi
if (isset($_POST['model_id_delete'])) {
    $id = intval($_POST['model_id_delete']);
    $sql = "DELETE FROM model WHERE model_id = ?";
    $adminclass->pdoDelete($sql, [$id]);
    header("Location: model.php");
    exit();
}
?>

<div class="content-wrapper">
  <section class="content-header"><h1>Model Yönetimi</h1></section>
  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
          <button class="btn btn-success" data-toggle="modal" data-target="#modal-model-add">Model Ekle</button>
        </div>
        <div class="card-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th><th>Marka</th><th>Model Ad</th><th>ERP Kodu</th><th>Fiyat</th><th>KDV</th><th>İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($modeller as $m): ?>
              <tr>
                <td><?= $m['model_id'] ?></td>
                 <td><?= htmlspecialchars($m['marka_ad']) ?></td>
                <td><?= htmlspecialchars($m['model_ad']) ?></td>
                <td><?= htmlspecialchars($m['logo_kod']) ?></td>
                <td><?= htmlspecialchars($m['model_fiyat']) ?></td>
                <td><?= htmlspecialchars($m['kdv']) ?></td>

                <td>
                  <button class="btn btn-warning" data-toggle="modal" data-target="#modal-edit-<?= $m['model_id']; ?>">Güncelle</button>
                  <button class="btn btn-danger" data-toggle="modal" data-target="#modal-delete-<?= $m['model_id']; ?>">Sil</button>
                </td>
              </tr>

              <!-- Güncelle Modal -->
                <div class="modal fade" id="modal-edit-<?= $m['model_id']; ?>">
                  <div class="modal-dialog"><div class="modal-content">
                    <form method="POST">
                      <div class="modal-header"><h4>Model Güncelle</h4></div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label>Marka</label>
                          <select name="marka_id" class="form-control" required>
                            <?php foreach ($markalar as $ma): ?>
                              <option value="<?= $ma['marka_id']; ?>" <?= $ma['marka_id'] == $m['marka_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ma['marka_ad']); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </div>

                        <div class="form-group">
                          <label>Model Adı</label>
                          <input type="text" name="model_ad" class="form-control" required value="<?= htmlspecialchars($m['model_ad']); ?>">
                        </div>
                        <div class="form-group">
                          <label>Logo Kod</label>
                          <input type="text" name="logo_kod" class="form-control" required value="<?= htmlspecialchars($m['logo_kod']); ?>">
                        </div>
                        <div class="form-group">
                          <label>Fiyat</label>
                          <input type="number" name="model_fiyat" class="form-control" value="<?= htmlspecialchars($m['model_fiyat']); ?>">
                        </div>
                        <div class="form-group">
                          <label>KDV</label>
                          <input type="number" name="kdv" class="form-control" value="<?= htmlspecialchars($m['kdv']); ?>">
                        </div>

                      </div>
                      <div class="modal-footer">
                        <input type="hidden" name="update" value="3002">
                        <input type="hidden" name="model_id" value="<?= $m['model_id']; ?>">
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                      </div>
                    </form>
                  </div></div>
                </div>


              <!-- Sil Modal -->
              <div class="modal fade" id="modal-delete-<?= $m['model_id']; ?>">
                <div class="modal-dialog"><div class="modal-content">
                  <form method="POST">
                    <div class="modal-header"><h4>Model Sil</h4></div>
                    <div class="modal-body">
                      <p><strong><?= htmlspecialchars($m['model_ad']); ?></strong> modelini silmek istediğinize emin misiniz?</p>
                      <input type="hidden" name="model_id_delete" value="<?= $m['model_id']; ?>">
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
                      <button type="submit" class="btn btn-danger">Sil</button>
                    </div>
                  </form>
                </div></div>
              </div>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
               <tr>
                  <th>ID</th><th>Marka</th><th>Model Ad</th><th>ERP Kodu<</th><th>Fiyat</th><th>KDV</th><th>İşlem</th>
                </tr>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Yeni Model Ekle Modal -->
<div class="modal fade" id="modal-model-add">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST">
      <div class="modal-header"><h4>Model Ekle</h4></div>
      <div class="modal-body">
        <div class="form-group">
          <label>Model Adı</label>
          <input type="text" name="model_ad" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Marka Seç</label>
          <select name="marka_id" class="form-control" required>
            <?php foreach ($markalar as $ma): ?>
              <option value="<?= $ma['marka_id']; ?>"><?= htmlspecialchars($ma['marka_ad']); ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-group">
                <label>Logo Kod</label>
                <input type="text" name="logo_kod" class="form-control" >
              </div>
            <div class="form-group">
                <label>Fiyat</label>
                <input type="number" name="model_fiyat" class="form-control" >
              </div>
              <div class="form-group">
                          <label>KDV</label>
                          <input type="number" name="kdv" class="form-control">
                        </div>

      </div>
      <div class="modal-footer">
        <input type="hidden" name="save" value="3001">
        <button type="submit" class="btn btn-success">Kaydet</button>
      </div>
    </form>
  </div></div>
</div>

<script>
// JSON olarak modelleri PHP’den JS’ye gönderiyoruz
const modeller = <?= json_encode($modeller); ?>;

// Fonksiyon: seçilen markaya göre modeller dropdown doldurma
function populateModels(modelSelect, modelAdInput, markaId, selectedModelId = null) {
  modelSelect.innerHTML = '';
  const filteredModels = modeller.filter(m => m.marka_id == markaId);

  filteredModels.forEach(m => {
    const option = document.createElement('option');
    option.value = m.model_id;
    option.textContent = m.model_ad;
    if (selectedModelId && m.model_id == selectedModelId) {
      option.selected = true;
      modelAdInput.value = m.model_ad;
    }
    modelSelect.appendChild(option);
  });

  if (!selectedModelId && filteredModels.length > 0) {
    modelSelect.value = filteredModels[0].model_id;
    modelAdInput.value = filteredModels[0].model_ad;
  }

  if (filteredModels.length === 0) {
    modelAdInput.value = '';
  }
}

// Sayfa yüklendiğinde tüm güncelleme modal dropdownlarını hazırla
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('show.bs.modal', function () {
      const markaSelect = modal.querySelector('.marka-select');
      const modelSelect = modal.querySelector('.model-select');
      const modelAdInput = modal.querySelector('input[name="model_ad"]');

      if (!markaSelect || !modelSelect || !modelAdInput) return;

      // Güncelleme formundaki mevcut model_id seçili değilse, modal açılmadan önce belirle
      let currentModelId = modelSelect.getAttribute('data-selected-model-id') || modelSelect.value;
      if (!currentModelId) {
        // modal açılırken, listedeki model_id değerini kullanabiliriz:
        const modelIdFromButton = markaSelect.getAttribute('data-model-id');
        currentModelId = modelIdFromButton || null;
      }

      // Modelleri doldur
      populateModels(modelSelect, modelAdInput, markaSelect.value, currentModelId);

      // Marka değiştiğinde modelleri tekrar yükle
      markaSelect.addEventListener('change', (e) => {
        populateModels(modelSelect, modelAdInput, e.target.value);
      });

      // Model seçimi değiştiğinde model adı inputunu güncelle
      modelSelect.addEventListener('change', (e) => {
        const selected = modeller.find(m => m.model_id == e.target.value);
        modelAdInput.value = selected ? selected.model_ad : '';
      });
    });
  });
});
</script>
