<?php include_once 'template/navbar.php'; ?>
<style>

.content-wrapper {
  margin-left: 0 !important;
  padding-left: 15px; /* İstersen */
}
@media (min-width: 1200px) {
  .modal-xxl {
    max-width: 95% !important; /* ekranın %95’ini kapla */
  }
}

</style>
<?php


// AdminClass örneği oluşturulmuş varsayalım
// $adminclass = new AdminClass();

// Hizmetleri, firmaları, markaları, modelleri ve türleri çekiyoruz.
// pdoQuery zaten array döndürüyor, o yüzden fetchAll() çağırma!
$admin = new AdminClass();
$admin->sadece_admin();
$pdo = $admin->getPdo();



// Tarih formatını SQL Server uyumlu hale getiren fonksiyon
function convertDateToSQL($date){
    if (!$date) return null;
    $parts = explode('/', $date);
    if(count($parts) !== 3) return null;
    return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
}
function formatDate($date) {
    if (!$date) return '';
    $d = new DateTime($date);
    return $d->format('d/m/Y');
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' 
    && isset($_POST['action']) 
    && $_POST['action'] == '15002' 
    && isset($_POST['abone_hizmet_id'])) {

    $abone_hizmet_id = intval($_POST['abone_hizmet_id']);
    $admin->deleteAboneHizmet($abone_hizmet_id);

    header('Location:sozlesmeler.php?deleted=1');
    exit;
}




// Form gönderildiyse işlemleri yap
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    // Master kayıt bilgileri
    $abone_hizmet_id = $_POST['abone_hizmet_id'] ?? null;
    $firma_id = intval($_POST['firma_id'] ?? 0);
    $fis_tarih = convertDateToSQL($_POST['fis_tarih'] ?? '');
    $detay = $_POST['detay'] ?? '';
    $aktif = isset($_POST['aktif']) ? intval($_POST['aktif']) : 1;

    if(empty($abone_hizmet_id)){
        // Yeni kayıt
        $stmt = $pdo->prepare("
              INSERT INTO abone_hizmet (firma_id, fis_tarih, detay, aktif) 
              VALUES (?, ?, ?, ?)
          ");
        $stmt->execute([$firma_id, $fis_tarih, $detay, $aktif]);

        $abone_hizmet_id = $pdo->lastInsertId();
    } else {
        // Mevcut kaydı güncelle
        $stmt = $pdo->prepare("UPDATE abone_hizmet SET firma_id=?, fis_tarih=?, detay=? WHERE abone_hizmet_id=?");
        $stmt->execute([$firma_id, $fis_tarih, $detay, $abone_hizmet_id]);

        // Önce eski hareketleri sil
        $stmt = $pdo->prepare("DELETE FROM abone_hizmet_hareket WHERE abone_hizmet_id=?");
        $stmt->execute([$abone_hizmet_id]);
    }

    // Detay hareketleri ekle
    if(isset($_POST['hareket']) && is_array($_POST['hareket'])){
        foreach($_POST['hareket'] as $h){
            $miktar = floatval($h['miktar'] ?? 0);
            $fiyat = floatval($h['fiyat'] ?? 0);
            $ind1   = floatval($h['indirim'] ?? 0);
            $ind2   = floatval($h['indirim_er'] ?? 0);
            $tutar = isset($h['tutar']) && $h['tutar'] !== ''
            ? floatval($h['tutar'])
            : 0;
            

            $stmt = $pdo->prepare("INSERT INTO abone_hizmet_hareket 
                (abone_hizmet_id, marka_id, model_id, aciklama, baslangic, bitis, dongu, miktar, fiyat, indirim, indirim_er, tutar, fatura, detay)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $abone_hizmet_id,
                $h['marka_id'] ?: null,
                $h['model_id'] ?: null,
                $h['aciklama'] ?? null,
                convertDateToSQL($h['baslangic'] ?? ''),
                convertDateToSQL($h['bitis'] ?? ''),
                $h['dongu'] ?? null,
                $miktar,
                $fiyat,
                $ind1,
                $ind2,
                $tutar,
                intval($h['fatura'] ?? 0),
                $h['detay'] ?? null
            ]);
        }
    }

    // **Yönlendirme**
    header('Location:sozlesmeler.php?success=1');
    exit;
}





if(isset($_POST['abone_hizmet_id_delete'])){
    $id = intval($_POST['abone_hizmet_id_delete']);

    try {
        // Önce hareketleri sil
        $stmt1 = $admin->pdo->prepare("DELETE FROM abone_hizmet_hareket WHERE abone_hizmet_id = ?");
        $stmt1->execute([$id]);

        // Sonra master kaydı sil
        $stmt2 = $admin->pdo->prepare("DELETE FROM abone_hizmet WHERE abone_hizmet_id = ?");
        $stmt2->execute([$id]);

        echo 'ok';
    } catch (PDOException $e){
        echo 'Hata: ' . $e->getMessage();
    }
    exit;
}



// Abone hizmetleri ve hareketlerini çek
$aktifHareketler = $admin->getAboneHizmetlerAktif();
$gelecekHareketler = $admin->getAboneHizmetlerGelecek();
$firmalar = $adminclass->pdoQuery("SELECT * FROM firma ORDER BY firma_ad");
$markalar = $adminclass->pdoQuery("SELECT * FROM marka ORDER BY marka_ad");
$modeller = $adminclass->pdoQuery("SELECT * FROM model ORDER BY model_ad");
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1>Sözleşme Yönetimi</h1></div>
        <div class="col-sm-6 text-right">
          <button class="btn btn-success" data-toggle="modal" data-target="#modal-add-abone">
            <i class="fa fa-plus"></i> Yeni Ekle
          </button>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title text-primary"><i class="fa fa-check-circle"></i> Aktif Sözleşmeler (Yürürlükte)</h3>
        </div>
        <div class="card-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th><th>Firma Adı</th><th>Marka</th><th>Model</th><th>Açıklama</th><th>Bitiş Tarihi</th><th>Tutar</th><th>Durum</th><th>İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($aktifHareketler as $row): ?>
              <tr 
                  data-detay='<?= htmlspecialchars(json_encode($admin->getAboneHizmetHareketler($row['abone_hizmet_id'])), ENT_QUOTES, 'UTF-8') ?>'
                  data-firma="<?= htmlspecialchars($row['firma_ad'], ENT_QUOTES, 'UTF-8') ?>"
                  data-fistarih="<?= htmlspecialchars($row['fis_tarih'], ENT_QUOTES, 'UTF-8') ?>"
              >
                  <td><?= htmlspecialchars($row['abone_hizmet_id']) ?></td>
                  <td><?= htmlspecialchars($row['firma_ad']) ?></td>
                  <td><?= htmlspecialchars($row['marka_ad']) ?></td>
                  <td><?= htmlspecialchars($row['model_ad']) ?></td>
                  <td><?= htmlspecialchars($row['aciklama']) ?></td>
                  <td><?= $row['bitis'] ? date('d/m/Y', strtotime($row['bitis'])) : '' ?></td>
                  <td><?= number_format($row['tutar'] ?? 0, 2, ',', '.') ?> ₺</td>
                  <td><span class="badge badge-success">Aktif</span></td>
                  <td>
                    <div style="display:flex; gap:5px;">
                      <button class="btn btn-info btn-sm btn-detay" data-toggle="modal" data-target="#modal-detay" data-aboneid="<?= $row['abone_hizmet_id'] ?>">Detay</button>
                      <form method="POST" onsubmit="return confirm('Silmek istiyor musunuz?');" style="margin:0;">
                        <input type="hidden" name="action" value="15002"><input type="hidden" name="abone_hizmet_id" value="<?= $row['abone_hizmet_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                      </form>
                    </div>
                  </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div style="height: 30px;"></div> <div class="card card-outline card-warning">
        <div class="card-header">
          <h3 class="card-title text-warning"><i class="fa fa-calendar-plus-o"></i> Gelecek Sözleşmeler (Henüz Başlamadı)</h3>
        </div>
        <div class="card-body">
          <table id="example2" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th><th>Firma Adı</th><th>Marka</th><th>Model</th><th>Açıklama</th><th>Başlangıç Tarihi</th><th>Tutar</th><th>Durum</th><th>İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($gelecekHareketler as $row): ?>
              <tr 
                  data-detay='<?= htmlspecialchars(json_encode($admin->getAboneHizmetHareketler($row['abone_hizmet_id'])), ENT_QUOTES, 'UTF-8') ?>'
                  data-firma="<?= htmlspecialchars($row['firma_ad'], ENT_QUOTES, 'UTF-8') ?>"
                  data-fistarih="<?= htmlspecialchars($row['fis_tarih'], ENT_QUOTES, 'UTF-8') ?>"
              >
                  <td><?= htmlspecialchars($row['abone_hizmet_id']) ?></td>
                  <td><?= htmlspecialchars($row['firma_ad']) ?></td>
                  <td><?= htmlspecialchars($row['marka_ad']) ?></td>
                  <td><?= htmlspecialchars($row['model_ad']) ?></td>
                  <td><?= htmlspecialchars($row['aciklama']) ?></td>
                  <td><span class="text-primary font-weight-bold"><?= $row['baslangic'] ? date('d/m/Y', strtotime($row['baslangic'])) : '' ?></span></td>
                  <td><?= number_format($row['tutar'] ?? 0, 2, ',', '.') ?> ₺</td>
                  <td><span class="badge badge-warning">Beklemede</span></td>
                  <td>
                    <div style="display:flex; gap:5px;">
                      <button class="btn btn-info btn-sm btn-detay" data-toggle="modal" data-target="#modal-detay" data-aboneid="<?= $row['abone_hizmet_id'] ?>">Detay</button>
                      <form method="POST" onsubmit="return confirm('Silmek istiyor musunuz?');" style="margin:0;">
                        <input type="hidden" name="action" value="15002"><input type="hidden" name="abone_hizmet_id" value="<?= $row['abone_hizmet_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                      </form>
                    </div>
                  </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- Tek modal -->
<div class="modal fade" id="modal-detay" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xxl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 id="modal-detay-title"></h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="modalHareketForm">
          <div id="modal-detay-body"></div>
          <button type="button" id="detayAddRow" class="btn btn-success btn-sm mt-2">+ Satır Ekle</button>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnKaydetHareket" class="btn btn-success">Kaydet</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>



<!-- Modal -->
<div class="modal fade" id="deleteModal<?= $row['abone_hizmet_id'] ?>" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Silme Onayı</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        ⚠️ Bu kaydı ve bağlı tüm hareketleri silmek üzeresiniz. Emin misiniz?
      </div>
      <div class="modal-footer">
        <form method="POST">
            <input type="hidden" name="action" value="15002">
            <input type="hidden" name="abone_hizmet_id" value="<?= $row['abone_hizmet_id'] ?>">
            <button type="submit" class="btn btn-danger">Evet, Sil</button>
        </form>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
      </div>
    </div>
  </div>
</div>





<!-- ABONE HİZMET EKLEME MODALI -->
<div class="modal fade" id="modal-add-abone" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xxl" role="document">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="abone_hizmet_id" value="">
        <div class="modal-header">
          <h5 class="modal-title">Yeni Abone Hizmet</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">

          <!-- Master -->
          <div class="row mb-3">
            <div class="col-md-6">
              <label>Firma</label>
              <select name="firma_id" class="form-control" required>
                <?php foreach($firmalar as $f): ?>
                  <option value="<?= $f['firma_id'] ?>"><?= $f['firma_ad'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label>Fiş Tarih</label>
              <input type="text" name="fis_tarih" class="form-control datepicker" value="<?= date('d/m/Y') ?>">
            </div>
          </div>

          <hr>

          <!-- Detaylar -->
          <table class="table table-sm table-borderless" id="hareketTable">
            <thead>
              <tr>
                <th>Marka</th>
                <th>Model</th>
                <th>Açıklama</th>
                <th>Başlangıç</th>
                <th>Bitiş</th>
                <th>Döngü</th>
                <th>Miktar</th>
                <th>Fiyat</th>
                <th>İndirim %</th>
                <th>İndirim ER %</th>
                <th>Tutar</th>
                <th>Fatura</th>
                <th>Detay</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>
                  <select name="hareket[0][marka_id]" class="form-control marka_select">
                    <option value="">Seçiniz</option>
                    <?php foreach($markalar as $m): ?>
                      <option value="<?= $m['marka_id'] ?>"><?= $m['marka_ad'] ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                  <select name="hareket[0][model_id]" class="form-control model_select">
                    <option value="">Önce marka seçiniz</option>
                  </select>
                </td>
                <td><input type="text" name="hareket[0][aciklama]" class="form-control"></td>
                <td><input type="text" name="hareket[0][baslangic]" class="form-control datepicker" placeholder="gg/aa/yyyy" autocomplete="off"></td>
                <td><input type="text" name="hareket[0][bitis]" class="form-control datepicker" placeholder="gg/aa/yyyy" autocomplete="off"></td>
                <td>
                  <select name="hareket[0][dongu]" class="form-control">
                    <option value="">Seçiniz</option>
                    <option value="Aylık">Aylık</option>
                    <option value="Yıllık">Yıllık</option>
                  </select>
                </td>
                <td><input type="number" name="hareket[0][miktar]" class="form-control"></td>
                <td><input type="text" name="hareket[0][fiyat]" class="form-control"></td>
                
                <td>
                  <div class="input-group">
                    <span class="input-group-text">%</span>
                    <input type="number" name="hareket[0][indirim]" class="form-control indirim" value="0">
                  </div>
                </td>

                <td>
                  <div class="input-group">
                    <span class="input-group-text">%</span>
                    <input type="number" name="hareket[0][indirim_er]" class="form-control indirim_er" value="0">
                  </div>
                </td>

                <td><input type="number" name="hareket[0][tutar]" class="form-control" readonly></td>
                <td>
                  <select name="hareket[0][fatura]" class="form-control">
                    <option value="0">Yok</option>
                    <option value="1">Var</option>
                  </select>
                </td>
                <td><input type="text" name="hareket[0][detay]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
              </tr>
            </tbody>
          </table>
          <button type="button" id="addRow" class="btn btn-success btn-sm">+ Satır Ekle</button>

            <div class="row mt-3">
              <div class="col-md-6"></div>

              <div class="col-md-6">
                <table class="table table-sm table-bordered">
                  <tbody>
                    <tr>
                      <th class="text-right bg-light">Brüt Toplam</th>
                      <td class="text-right font-weight-bold" id="addOzetBrut">0.00</td>
                    </tr>
                    <tr>
                      <th class="text-right bg-light">KDV (Faturalı)</th>
                      <td class="text-right font-weight-bold" id="addOzetKdv">0.00</td>
                    </tr>
                    <tr class="table-success">
                      <th class="text-right">Genel Toplam</th>
                      <td class="text-right font-weight-bold" id="addOzetGenel">0.00</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>







<!-- JS Bölümü -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" />
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function(){

    const allModeller = <?= json_encode($modeller); ?>;
    const allMarkalar = <?= json_encode($markalar); ?>;
    let rowIndex = 1;
    let currentAboneId = null; // global değişken

    

    // Datepicker başlatma fonksiyonu
    function initDatepicker(row){
        row.find('.datepicker').datepicker({
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true
        });
    }

    
    // ---------- Modal açıldığında ilk satırın datepicker'ını başlat ----------
    $('#modal-add-abone').on('shown.bs.modal', function(){
        const firstRow = $(this).find('#hareketTable tbody tr:first');
        initDatepicker(firstRow);
    });
    
       // Satır ekleme
    $('#addRow').click(function(){
        let tbody = $('#hareketTable tbody');
        let newRow = tbody.find('tr:first').clone();

        newRow.find('input, select').each(function(){
            $(this).val('');
            let name = $(this).attr('name');
            if(name) $(this).attr('name', name.replace(/\[\d+\]/, '['+rowIndex+']'));
        });

        newRow.find('.model_select').html('<option value="">Önce marka seçiniz</option>');

        tbody.append(newRow);
        initDatepicker(newRow);
        rowIndex++;
    });

    // Satır silme
    $(document).on('click', '.removeRow', function(){
        if($('#hareketTable tbody tr').length > 1){
            $(this).closest('tr').remove();
        }
    });

    // Marka değişince modele filtrele
    $(document).on('change', '.marka_select', function(){
        const row = $(this).closest('tr');
        const selectedMarkaId = $(this).val();
        const modelSelect = row.find('.model_select');

        modelSelect.html('<option value="">Seçiniz</option>');
        if(selectedMarkaId){
            const filtered = allModeller.filter(m => m.marka_id == selectedMarkaId);
            filtered.forEach(m => {
                modelSelect.append('<option value="'+m.model_id+'">'+m.model_ad+'</option>');
            });
        } else {
            modelSelect.html('<option value="">Önce marka seçiniz</option>');
        }
    });



    // --- Ortak: Modal içindeyken Enter'ı engelle ---
    $(document).on('keydown', '.modal', function(e){
        const active = document.activeElement;
        if(e.key === 'Enter' && active.tagName !== 'TEXTAREA' && active.tagName !== 'BUTTON'){
            e.preventDefault();
            e.stopPropagation();
        }
    });
    
// Modal içindeki fiyat alanına * yazılırsa model fiyatını getir
$(document).on('input', '#modal-detay-body .fiyat', function () {

    const input = $(this);
    const row   = input.closest('tr');
    const val   = input.val().trim();

    if (val !== '*') return;

    const modelId = row.find('.model_select').val();
    if (!modelId) {
        input.val('');
        alert('Önce model seçiniz!');
        return;
    }

    const model = allModeller.find(m => String(m.model_id) === String(modelId));

    if (!model || model.model_fiyat == null) {
        input.val('');
        row.find('.tutar').val('0.00');
        alert('Bu modelin fiyatı yok!');
        return;
    }

    input.val(parseFloat(model.model_fiyat).toFixed(2));

    // 🔁 Her değişiklikten sonra tek merkez
    hesaplaToplam();
});
$(document).on('input', '#modal-add-abone input[name*="[fiyat]"]', function () {

    const input = $(this);
    const row   = input.closest('tr');
    const val   = input.val().trim();

    if (val !== '*') return;

    const modelId = row.find('.model_select').val();
    if (!modelId) {
        input.val('');
        alert('Önce model seçiniz!');
        return;
    }

    const model = allModeller.find(m => String(m.model_id) === String(modelId));

    if (!model || model.model_fiyat == null) {
        input.val('');
        alert('Bu modelin fiyatı yok!');
        return;
    }

    input.val(parseFloat(model.model_fiyat).toFixed(2));
    hesaplaAddAboneToplam();
});



// Başlangıç tarihi seçilince bitiş tarihini 1 yıl ilerisine otomatik atama
$(document).on('change', '#modal-detay-body .baslangic', function () {

    const row = $(this).closest('tr');
    const val = $(this).val();

    if (!val) return;

    const p = val.split('/');
    if (p.length !== 3) return;

    const d = new Date(p[2], p[1] - 1, p[0]);
    d.setFullYear(d.getFullYear() + 1);

    const bitis =
        String(d.getDate()).padStart(2, '0') + '/' +
        String(d.getMonth() + 1).padStart(2, '0') + '/' +
        d.getFullYear();

    row.find('.bitis').val(bitis);
    row.find('.dongu').val('Yıllık');
    row.find('.miktar').val(1);
    row.find('.fatura').val('1');

    hesaplaToplam();
});












    
// Tekil modal açılınca verileri doldur
$(document).on('click', '.btn-detay', function(){
    const $row = $(this).closest('tr');
    currentAboneId = $(this).data('aboneid') || null;

    // Firma adı: data-firma hem .data hem .attr ile denenir
    const firma = $row.data('firma') || $row.attr('data-firma') || '';

    // Fiş tarihini farklı yazım şekillerinden yakala
    let fisTarihRaw =
        $row.data('fistarih') ||                 // data-fistarih
        $row.attr('data-fistarih') ||            // data-fistarih
        $row.attr('data-fis_tarih') ||           // data-fis_tarih
        $row.attr('data-fis-tarih') || '';       // data-fis-tarih

    // Tarih formatlayıcı
    function formatDate(s){
        if(!s) return '';
        const p = String(s).split(/[-\/]/);
        if(p.length === 3){
            // yyyy-mm-dd veya yyyy/mm/dd → dd/mm/yyyy
            if(p[0].length === 4) return `${p[2]}/${p[1]}/${p[0]}`;
            // dd-mm-yyyy → dd/mm/yyyy
            if(p[2].length === 4) return `${p[0]}/${p[1]}/${p[2]}`;
        }
        return s; // tanınmazsa dokunma
    }
    const fisTarih = formatDate(fisTarihRaw);

    // Başlık: Firma + (Fiş: …)
    $('#modal-detay-title').text(
        `Hizmet Detayları - ${firma}${fisTarih ? ' (Fiş: ' + fisTarih + ')' : ''}`
    );

    // Detayları al
    let detaylar = [];
    const detayAttr = $row.attr('data-detay') || '[]';
    try { detaylar = JSON.parse(detayAttr); } 
    catch(e){ alert('Detaylar yüklenemedi'); return; }

    // Tabloyu çiz
    let html = '<table id="detayTable" class="table table-sm table-bordered"><thead><tr>'+
        '<th>ID</th><th>Marka</th><th>Model</th><th>Açıklama</th><th>Başlangıç</th><th>Bitiş</th>'+
        '<th>Döngü</th><th>Miktar</th><th>Fiyat</th><th>İndirim %</th><th>İndirim ER %</th><th>Tutar</th><th>Fatura</th><th>Detay</th><th></th></tr></thead><tbody>';

    detaylar.forEach(d=>{
        const baslangic = formatDate(d.baslangic || '');
        const bitis     = formatDate(d.bitis || '');

        const faturaYok = (d.fatura == 0 || d.fatura === "0") ? 'selected' : '';
        const faturaVar = (d.fatura == 1 || d.fatura === "1") ? 'selected' : '';

        // Marka seçenekleri
        let markaOptions = '<option value="">Seçiniz</option>';
        allMarkalar.forEach(m=>{
            markaOptions += `<option value="${m.marka_id}" ${String(d.marka_id)==String(m.marka_id)?'selected':''}>${m.marka_ad}</option>`;
        });

        // Model seçenekleri (seçili markaya göre filtre)
        let modelOptions = '<option value="">Önce marka seçiniz</option>';
        allModeller.filter(x=> String(x.marka_id) === String(d.marka_id))
            .forEach(m=>{
                modelOptions += `<option value="${m.model_id}" ${String(d.model_id)==String(m.model_id)?'selected':''}>${m.model_ad}</option>`;
            });

        html += `<tr data-id="${d.abone_hizmet_hareket_id}">
            <td><input type="text" class="form-control abone_hareket_id" value="${d.abone_hizmet_hareket_id}" readonly></td>
            <td><select class="form-control marka_select">${markaOptions}</select></td>
            <td><select class="form-control model_select">${modelOptions}</select></td>
            <td><input type="text" class="form-control aciklama" value="${d.aciklama||''}"></td>
            <td><input type="text" class="form-control baslangic datepicker" value="${baslangic}"></td>
            <td><input type="text" class="form-control bitis datepicker" value="${bitis}"></td>
            <td>
                <select class="form-control dongu">
                    <option value="">Seçiniz</option>
                    <option value="Aylık"  ${d.dongu=='Aylık'?'selected':''}>Aylık</option>
                    <option value="Yıllık" ${d.dongu=='Yıllık'?'selected':''}>Yıllık</option>
                </select>
            </td>
            <td><input type="number" class="form-control miktar" value="${d.miktar||0}"></td>
            <td><input type="text" class="form-control fiyat"  value="${d.fiyat||0}"></td>
            <td>
                <div class="input-group">
                    <span class="input-group-text">%</span>
                    <input type="number" class="form-control indirim" value="${d.indirim||0}">
                </div>
            </td>

            <td>
                <div class="input-group">
                    <span class="input-group-text">%</span>
                    <input type="number" class="form-control indirim_er" value="${d.indirim_er||0}">
                </div>
            </td>
            <td><input type="number" class="form-control tutar"  value="${d.tutar||0}" readonly></td>
            <td>
                <select class="form-control fatura">
                    <option value="0" ${faturaYok}>Yok</option>
                    <option value="1" ${faturaVar}>Var</option>
                </select>
            </td>
            <td><input type="text" class="form-control detay" value="${d.detay||''}"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        </tr>`;
    });

    html += '</tbody></table>';
    html += `
<div style="height:12px"></div>

<table class="table table-sm table-bordered w-50 ml-auto">
  <tbody>
    <tr>
      <th class="text-right bg-light">Brüt Toplam</th>
      <td class="text-right font-weight-bold" id="ozetBrut">0.00</td>
    </tr>
    <tr>
      <th class="text-right bg-light">KDV (Faturalı)</th>
      <td class="text-right font-weight-bold" id="ozetKdv">0.00</td>
    </tr>
    <tr class="table-success">
      <th class="text-right">Genel Toplam</th>
      <td class="text-right font-weight-bold" id="ozetGenel">0.00</td>
    </tr>
  </tbody>
</table>
`;

    $('#modal-detay-body').html(html);
    initDatepicker($('#modal-detay-body'));
    hesaplaToplam();

});

// Satır ekleme - Detay modalında
$(document).on('click', '#detayAddRow', function(){
    const tbody = $('#modal-detay-body #detayTable tbody');
    if(!tbody.length) return;

    let markaOptions = '<option value="">Seçiniz</option>';
    allMarkalar.forEach(m=>{
        markaOptions += `<option value="${m.marka_id}">${m.marka_ad}</option>`;
    });

    let newRow = `
    <tr data-id="0">
        <td><input type="text" class="form-control ID" readonly></td>
        <td><select class="form-control marka_select">${markaOptions}</select></td>
        <td><select class="form-control model_select"><option value="">Önce marka seçiniz</option></select></td>
        <td><input type="text" class="form-control aciklama"></td>
        <td><input type="text" class="form-control baslangic datepicker"></td>
        <td><input type="text" class="form-control bitis datepicker"></td>
        <td>
            <select class="form-control dongu">
                <option value="">Seçiniz</option>
                <option value="Aylık">Aylık</option>
                <option value="Yıllık">Yıllık</option>
            </select>
        </td>
        <td><input type="number" class="form-control miktar" value="1"></td>
        <td><input type="number" class="form-control fiyat" value="0"></td>
        <td><input type="number" class="form-control indirim" value="0"></td>
        <td><input type="number" class="form-control indirim_er" value="0"></td>
        <td><input type="number" class="form-control tutar" readonly></td>
        <td>
            <select class="form-control fatura">
                <option value="0" selected>Yok</option>
                <option value="1">Var</option>
            </select>
        </td>
        <td><input type="text" class="form-control detay"></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
    </tr>
    `;

    tbody.append(newRow);
    initDatepicker(tbody.find('tr:last'));
});


    // Satır silme
    $(document).on('click', '.removeRow', function(){
        const tbody = $('#modal-detay-body table tbody');
        if(tbody.find('tr').length > 1){
            $(this).closest('tr').remove();
        }
    });

    // Marka → Model filtreleme
    $(document).on('change', '.marka_select', function(){
        const row = $(this).closest('tr');
        const selected = $(this).val();
        const modelSelect = row.find('.model_select');
        modelSelect.html('<option value="">Seçiniz</option>');
        if(selected){
            allModeller.filter(x=>x.marka_id==selected).forEach(m=>{
                modelSelect.append(`<option value="${m.model_id}">${m.model_ad}</option>`);
            });
        }
    });

// Miktar × Fiyat → Tutar (Zincirleme İndirim)
$(document).on(
    'input change',
    '#modal-detay-body .miktar, #modal-detay-body .fiyat, #modal-detay-body .indirim, #modal-detay-body .indirim_er, #modal-detay-body .fatura',
    function () {
        hesaplaToplam();
    }
);

function hesaplaToplam(){

    let brutToplam = 0;
    let kdvToplam  = 0;

    $('#modal-detay-body table tbody tr').each(function(){

        const row = $(this);

        const miktar = parseFloat(row.find('.miktar').val()) || 0;
        const fiyat  = parseFloat(row.find('.fiyat').val())  || 0;
        const ind1   = parseFloat(row.find('.indirim').val()) || 0;
        const ind2   = parseFloat(row.find('.indirim_er').val()) || 0;
        const fatura = row.find('.fatura').val();

        // 🔹 Brüt
        let brut = miktar * fiyat;

        // 🔹 1. indirim
        let after1 = brut - (brut * ind1 / 100);

        // 🔹 2. indirim (kalan üzerinden)
        let after2 = after1 - (after1 * ind2 / 100);

        if (after2 < 0) after2 = 0;

        // Satır tutarı
        row.find('.tutar').val(after2.toFixed(2));

        brutToplam += after2;

        if (fatura === "1") {
            kdvToplam += after2 * 0.20;
        }
    });

    // 🔻 ALT ÖZET (ilk verdiğin HTML’e birebir)
    $('#ozetBrut').text(brutToplam.toFixed(2));
    $('#ozetKdv').text(kdvToplam.toFixed(2));
    $('#ozetGenel').text((brutToplam + kdvToplam).toFixed(2));
}

function hesaplaAddAboneToplam() {

    let brutToplam = 0;
    let kdvToplam  = 0;

    $('#hareketTable tbody tr').each(function(){

        const row = $(this);

        const miktar    = parseFloat(row.find('[name*="[miktar]"]').val()) || 0;
        const fiyat     = parseFloat(row.find('[name*="[fiyat]"]').val()) || 0;
        const indirim   = parseFloat(row.find('[name*="[indirim]"]').val()) || 0;
        const indirimER = parseFloat(row.find('[name*="[indirim_er]"]').val()) || 0;
        const fatura    = row.find('[name*="[fatura]"]').val();

        // 1️⃣ Brüt
        let brut = miktar * fiyat;
        if (brut < 0) brut = 0;

        // 2️⃣ İlk indirim (brüt üzerinden)
        let indirim1Tutar = brut * (indirim / 100);
        let araTutar = brut - indirim1Tutar;

        // 3️⃣ ER indirimi (kalan üzerinden)
        let indirimERTutar = araTutar * (indirimER / 100);
        let net = araTutar - indirimERTutar;

        if (net < 0) net = 0;

        row.find('[name*="[tutar]"]').val(net.toFixed(2));

        brutToplam += net;

        if (fatura === "1") {
            kdvToplam += net * 0.20;
        }
    });

    $('#addOzetBrut').text(brutToplam.toFixed(2));
    $('#addOzetKdv').text(kdvToplam.toFixed(2));
    $('#addOzetGenel').text((brutToplam + kdvToplam).toFixed(2));
}


$(document).on(
    'input change',
    '#hareketTable input, #hareketTable select',
    function () {
        hesaplaAddAboneToplam();
    }
);




    // Kaydet
    $('#btnKaydetHareket').click(function(){
        let payload = [];
        $('#modal-detay-body tr').each(function(){
            const tr = $(this);
            const marka_id = tr.find('.marka_select').val();

            // Boş satırları atla
            if(!marka_id) return;

            payload.push({
                abone_hizmet_hareket_id: tr.data('id') || 0,
                abone_hizmet_id: currentAboneId,
                marka_id: marka_id,
                model_id: tr.find('.model_select').val(),
                aciklama: tr.find('.aciklama').val(),
                baslangic: tr.find('.baslangic').val(),
                bitis: tr.find('.bitis').val(),
                dongu: tr.find('.dongu').val(),
                miktar: tr.find('.miktar').val(),
                fiyat: tr.find('.fiyat').val(),
                indirim: tr.find('.indirim').val(),         
                indirim_er: tr.find('.indirim_er').val(), 
                tutar: tr.find('.tutar').val(),
                fatura: tr.find('.fatura').val(),
                detay: tr.find('.detay').val()
            });
        });

        if(payload.length === 0){
            alert('Kaydedilecek bir satır yok!');
            return;
        }

        $.post('ajax_hareket_guncelle.php', {hareketler: payload}, function(res){
           
            $('#modal-detay').modal('hide');
            location.reload();
        });
    });
    // Satır silme ve DB'den silme
$(document).on('click', '.removeRow', function(){
    const tr = $(this).closest('tr');
    const id = tr.data('id'); // abone_hizmet_hareket_id

    // En az bir satır olmalı
    if($('#modal-detay-body tr').length <= 1){
        alert('En az bir satır olmalı!');
        return;
    }

           if(id && id != 0){
        // DB'de kayıtlı, AJAX ile sil
        $.post('ajax_hareket_sil.php', {id: id}, function(res){
            if(res.success){
                tr.remove();
                // alert isteğe bağlı, eklemeye gerek yok
            } else {
                alert('Silme işlemi başarısız: ' + res.error);
            }
        }, 'json');
    } else {
        // Sadece HTML'den kaldır
        tr.remove();
    }
});




$(function () {
    // Üstteki tablo (Zaten var olduğunu varsayıyorum)
    if (!$.fn.DataTable.isDataTable('#example1')) {
        $("#example1").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "buttons": ["csv", "excel", "pdf"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    }

    // ALTAKİ TABLO İÇİN YENİ EKLENECEK KISIM
    $("#example2").DataTable({
        "responsive": true, 
        "lengthChange": true, 
        "autoWidth": false,
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json" // Türkçe dil desteği için
        },
        "buttons": ["csv", "excel", "pdf"] // Eğer butonları da istiyorsan
    }).buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');
});



});
</script>















</body>
</html>
