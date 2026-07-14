<?php include_once 'template/navbar2.php'; ?>
<?php include_once 'template/sidebar.php'; ?>
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





// Form gönderildiyse işlemler
if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $teklif_id = $_POST['teklif_id'] ?? null;
    $firma_id  = intval($_POST['firma_id'] ?? 0);
    $fis_tarih = convertDateToSQL($_POST['fis_tarih'] ?? '');
    $detay     = $_POST['detay'] ?? '';
    $aktif = array_key_exists('aktif', $_POST) && $_POST['aktif'] !== ''
    ? intval($_POST['aktif'])
    : null;



    if(empty($teklif_id)){
        // Yeni teklif ekle
        $stmt = $pdo->prepare("  INSERT INTO teklif (firma_id, fis_tarih, detay, durum, aktif)  VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([  $firma_id,  $fis_tarih,  $detay,  'Beklemede',  $aktif]);
        $teklif_id = $pdo->lastInsertId();
    } else {
        // Mevcut teklifi güncelle
        $stmt = $pdo->prepare("  UPDATE teklif  SET firma_id=?, fis_tarih=?, detay=?, aktif=?  WHERE teklif_id=?");
        $stmt->execute([  $firma_id,  $fis_tarih,  $detay,  $aktif,  $teklif_id]);

        // Önce eski hareketleri sil
        $stmt = $pdo->prepare("DELETE FROM teklif_hareket WHERE teklif_id=?");
        $stmt->execute([$teklif_id]);
    }

    // Hareketleri ekle
    if(isset($_POST['hareket']) && is_array($_POST['hareket'])){
        foreach($_POST['hareket'] as $h){
            $miktar = floatval($h['miktar'] ?? 0);
            $fiyat  = floatval($h['fiyat'] ?? 0);
            
            $indirim    = isset($h['indirim']) && $h['indirim'] !== '' 
                ? floatval($h['indirim']) 
                : 0;

            $indirim_er = isset($h['indirim_er']) && $h['indirim_er'] !== '' 
                ? floatval($h['indirim_er']) 
                : 0;

            $tutar = isset($h['tutar']) && $h['tutar'] !== ''
            ? floatval($h['tutar'])
            : 0;
            
            $stmt = $pdo->prepare("INSERT INTO teklif_hareket
                (teklif_id, indirim, indirim_er, marka_id, model_id, aciklama, baslangic, bitis, dongu, miktar, fiyat, tutar, fatura, detay)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $teklif_id,
                $indirim,
                $indirim_er,
                $h['marka_id'] ?: null,
                $h['model_id'] ?: null,
                $h['aciklama'] ?? null,
                convertDateToSQL($h['baslangic'] ?? ''),
                convertDateToSQL($h['bitis'] ?? ''),
                $h['dongu'] ?? null,
                $miktar,
                $fiyat,
                $tutar,
                intval($h['fatura'] ?? 0),
                $h['detay'] ?? null
            ]);

        }
    }

    header('Location: teklif_bekleyen.php?success=1');
    exit;
}

// Tarih filtreleme
$baslangic = $_GET['baslangic'] ?? date('Y-m-d');
$bitis     = $_GET['bitis'] ?? date('Y-m-d', strtotime('+30 days'));

// Biten veya bitmek üzere olan hizmetler
$hareketler = $admin->getBitenAboneHizmetlerCustom($baslangic, $bitis);

// Markalar, modeller ve firmalar
$firmalar = $admin->pdoQuery("SELECT * FROM firma ORDER BY firma_ad");
$markalar = $admin->pdoQuery("SELECT * FROM marka ORDER BY marka_ad");
$modeller = $admin->pdoQuery("SELECT * FROM model ORDER BY model_ad");
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Biten / Bitmek Üzere Olan Sözleşmeler</h1>
  </section>
  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <!-- Sola yaslı -->
            <button class="btn btn-success" data-toggle="modal" data-target="#modal-add-abone">
              Teklif Oluştur
            </button>

                  <!-- Ortalanmış -->
        <div class="mx-auto">
          <a href="teklif_ekleme.php" class="btn btn-outline-dark mr-2">Teklif Ver</a>
          <a href="teklif_bekleyen.php" class="btn btn-outline-dark mr-2">Bekleyen Teklifler</a>
          <a href="teklif_genel.php" class="btn btn-outline-dark">Bütün Teklifler</a>
      </div>
          </div>
        <div class="card-body">

          <!-- Filtre -->
          <form method="GET" class="form-inline mb-3">
            <label>Başlangıç: </label>
            <input type="date" name="baslangic" class="form-control mx-2"
                   value="<?= htmlspecialchars($_GET['baslangic'] ?? '') ?>">
            <label>Bitiş: </label>
            <input type="date" name="bitis" class="form-control mx-2"
                   value="<?= htmlspecialchars($_GET['bitis'] ?? '') ?>">
            <button type="submit" class="btn btn-primary">Filtrele</button>
          </form>

          <!-- Tablo -->
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Firma Adı</th>
                <th>Marka</th>
                <th>Model</th>
                <th>Açıklama</th>
                <th>Bitiş Tarihi</th>
                <th>Tutar</th>
                <th>İşlemler</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($hareketler as $row): ?>
              <tr data-firma="<?= htmlspecialchars($row['firma_ad']) ?>"
                  data-firma-id="<?= htmlspecialchars($row['firma_id']) ?>"
                  data-fistarih="<?= htmlspecialchars($row['fis_tarih'], ENT_QUOTES, 'UTF-8') ?>"
                  data-detay='<?= htmlspecialchars(json_encode($admin->getAboneHizmetHareketler($row['abone_hizmet_id'])), ENT_QUOTES) ?>'>
                <td><?= htmlspecialchars($row['abone_hizmet_id']) ?></td>
                <td><?= htmlspecialchars($row['firma_ad']) ?></td>
                <td><?= htmlspecialchars($row['marka_ad']) ?></td>
                <td><?= htmlspecialchars($row['model_ad']) ?></td>
                <td><?= htmlspecialchars($row['aciklama']) ?></td>
                <td><?= $row['bitis'] ? date('d/m/Y', strtotime($row['bitis'])) : '' ?></td>
                <td><?= number_format($row['tutar'] ?? 0, 2, ',', '.') ?> ₺</td>
                <td>
                      <button class="btn btn-info btn-sm btn-detay" data-toggle="modal" data-target="#modal-detay"
                        data-firma="<?= htmlspecialchars($row['firma_ad'], ENT_QUOTES) ?>"  
                        data-detay='<?= htmlspecialchars(json_encode($admin->getAboneHizmetHareketler($row['abone_hizmet_id'])), ENT_QUOTES) ?>'  
                        data-aboneid="<?= $row['abone_hizmet_id'] ?>"
                    >Detay</button>
  </td>
                          
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

<!-- Detay Modal -->

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
        <button type="button" id="btnKaydetHareket" class="btn btn-warning">Teklif Ver</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
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
                <th>İndirim</th>
                <th>İndirim Türü</th>
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
                  <input type="number" step="0.01" name="hareket[0][indirim]" class="form-control" placeholder="%">
                </td>
                <td>
                  <input type="number" step="0.01" name="hareket[0][indirim_er]" class="form-control" placeholder="%">
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


$(document).on(
  'input change',
  '.miktar, .fiyat, .indirim, .indirim_er, .fatura',
  function(){

    const row = $(this).closest('tr');

    const miktar = parseFloat(row.find('.miktar').val()) || 0;
    const fiyat  = parseFloat(row.find('.fiyat').val()) || 0;
    const ind1   = parseFloat(row.find('.indirim').val()) || 0;
    const ind2   = parseFloat(row.find('.indirim_er').val()) || 0;

    // 1️⃣ Brüt
    let brut = miktar * fiyat;

    // 2️⃣ 1. indirim
    let afterInd1 = brut - (brut * ind1 / 100);

    // 3️⃣ 2. indirim (kalan üzerinden)
    let afterInd2 = afterInd1 - (afterInd1 * ind2 / 100);

    if(afterInd2 < 0) afterInd2 = 0;

    row.find('.tutar').val(afterInd2.toFixed(2));

    hesaplaToplam();
});


    



    // --- Ortak: Modal içindeyken Enter'ı engelle ---
    $(document).on('keydown', '.modal', function(e){
        const active = document.activeElement;
        if(e.key === 'Enter' && active.tagName !== 'TEXTAREA' && active.tagName !== 'BUTTON'){
            e.preventDefault();
            e.stopPropagation();
        }
    });

// Fiyat inputuna * yazılırsa model fiyatını otomatik getir
$(document).on('input', '#modal-detay .fiyat, #modal-add-abone input[name*="[fiyat]"]', function(){
    const input = $(this);
    const row = input.closest('tr');
    const val = input.val().trim();

    if(val === '*'){
        const modelId = row.find('.model_select').val();
        if(modelId){
            const model = allModeller.find(m => m.model_id == modelId);
            if(model && model.model_fiyat !== undefined && model.model_fiyat !== null){
                const fiyat = parseFloat(model.model_fiyat) || 0;
                input.val(fiyat.toFixed(2));

                // Modal farkına göre miktar/tutar alanlarını bul
                let miktarInput, tutarInput;
                if(input.closest('#modal-detay').length){
                    miktarInput = row.find('.miktar');
                    tutarInput  = row.find('.tutar');
                } else {
                    miktarInput = row.find('input[name*="[miktar]"]');
                    tutarInput  = row.find('input[name*="[tutar]"]');
                }

                const miktar = parseFloat(miktarInput.val()) || 0;
                tutarInput.val((miktar * fiyat).toFixed(2));

                console.log('Fiyat * ile alındı:', {
                    modal: input.closest('.modal').attr('id'),
                    id: model.model_id,
                    ad: model.model_ad,
                    fiyat: fiyat
                });
            } else {
                input.val('');
                if(input.closest('#modal-detay').length){
                    row.find('.tutar').val('0.00');
                } else {
                    row.find('input[name*="[tutar]"]').val('0.00');
                }
                alert('Bu modelin fiyatı yok!');
            }
        } else {
            input.val('');
            alert('Önce model seçiniz!');
        }
    }
});


function hesaplaToplam(){

    let toplamMiktar = 0;
    let toplamFiyat = 0;
    let toplamIndirim = 0;
    let toplamTutar = 0;

    let brutToplam = 0;
    let kdvToplam = 0;

    $('#modal-detay-body table tbody tr').each(function(){

        const row = $(this);

        const miktar = parseFloat(row.find('.miktar').val()) || 0;
        const fiyat  = parseFloat(row.find('.fiyat').val()) || 0;
        const ind1   = parseFloat(row.find('.indirim').val()) || 0;
        const ind2   = parseFloat(row.find('.indirim_er').val()) || 0;
        const fatura = row.find('.fatura').val();

        // 🔹 BRÜT
        let brut = miktar * fiyat;

        // 🔹 1. indirim
        let afterInd1 = brut - (brut * ind1 / 100);

        // 🔹 2. indirim (kalan üzerinden)
        let afterInd2 = afterInd1 - (afterInd1 * ind2 / 100);

        if(afterInd2 < 0) afterInd2 = 0;

        let satirIndirim = brut - afterInd2;

        // TOPLAMLAR
        toplamMiktar += miktar;
        toplamFiyat  += brut;
        toplamIndirim += satirIndirim;
        toplamTutar  += afterInd2;

        brutToplam += afterInd2;

        if(fatura == "1"){
            kdvToplam += afterInd2 * 0.20;
        }
    });

    // ÜST TOPLAM SATIRI
    $('#toplamMiktar').text(toplamMiktar);
    $('#toplamFiyat').text(toplamFiyat.toFixed(2));
    $('#toplamIndirim').text(toplamIndirim.toFixed(2));
    $('#toplamTutar').text(toplamTutar.toFixed(2));

    // ALT ÖZET
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


// Modal içindeki fiyat alanına * yazılırsa model fiyatını getir
$(document).on('input', '#modal-detay-body .fiyat', function(){
    const input = $(this);
    const row = input.closest('tr');
    const val = input.val().trim();

    if(val === '*'){
        const modelId = row.find('.model_select').val();
        if(modelId){
            const model = allModeller.find(m => m.model_id == modelId);
            if(model && model.model_fiyat !== undefined && model.model_fiyat !== null){
                const fiyat = parseFloat(model.model_fiyat) || 0;
                input.val(fiyat.toFixed(2));

                // Tutarı güncelle
                const miktar = parseFloat(row.find('.miktar').val()) || 0;
                row.find('.tutar').val((miktar * fiyat).toFixed(2));

                console.log('Fiyat * ile alındı (modal):', {
                    id: model.model_id,
                    ad: model.model_ad,
                    fiyat: fiyat
                });
            } else {
                input.val('');
                row.find('.tutar').val('0.00');
                alert('Bu modelin fiyatı yok!');
            }
        } else {
            input.val('');
            alert('Önce model seçiniz!');
        }
    }
});





// Başlangıç tarihi seçilince bitiş tarihini 1 yıl ilerisine otomatik atama
$(document).on('change', '.baslangic', function(){
    const row = $(this).closest('tr');
    const baslangicVal = $(this).val();

    if(baslangicVal){
        const parts = baslangicVal.split('/');
        if(parts.length === 3){
            const day = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1; // JS months 0-11
            const year = parseInt(parts[2], 10);

            const baslangicDate = new Date(year, month, day);
            const bitisDate = new Date(baslangicDate);
            bitisDate.setFullYear(bitisDate.getFullYear() + 1);

            bitisDate.setDate(bitisDate.getDate() - 1);

            const bitisStr = ("0" + bitisDate.getDate()).slice(-2) + "/" +
                             ("0" + (bitisDate.getMonth() + 1)).slice(-2) + "/" +
                             bitisDate.getFullYear();

            // Bitiş tarihini otomatik ata
            row.find('input[name*="[bitis]"]').val(bitisStr);

            // Döngü ve miktar otomatik ayarlama
            row.find('select[name*="[dongu]"]').val('Yıllık');
            row.find('input[name*="[miktar]"]').val(1);

            // Fatura varsayılan olarak “Var” yap
            row.find('select[name*="[fatura]"]').val('1');
        }
    }
});






// Tekil modal açılınca verileri doldur
    // Tekil modal açılınca verileri doldur
$(document).on('click', '.btn-detay', function(){
    const $row = $(this).closest('tr');

    // Firma adı ve ID
    const currentFirmaAd = $row.data('firma') || '';
    const currentFirmaId = $row.data('firma-id') || null;
    
    // Abone ID
    currentAboneId = $(this).data('aboneid') || null;

    // Fiş tarihi
    const fisTarihRaw = $row.data('fistarih') || $row.attr('data-fistarih') || '';
    const formatDate = s => {
        if(!s) return '';
        const p = String(s).split(/[-\/]/);
        if(p.length === 3){
            if(p[0].length === 4) return `${p[2]}/${p[1]}/${p[0]}`;
            if(p[2].length === 4) return `${p[0]}/${p[1]}/${p[2]}`;
        }
        return s;
    };
    const fisTarih = formatDate(fisTarihRaw);

    // Modal başlığı
    $('#modal-detay-title').text(
        `Hizmet Detayları - ${currentFirmaAd} [ID: ${currentFirmaId}]${fisTarih ? ' (Fiş: '+fisTarih+')' : ''}`
    );

    // Modal veri attribute'ları
    $('#modal-detay').data('fistarih', fisTarih);
    $('#modal-detay').data('firma-id', currentFirmaId);

    // Detayları al
    let detaylar = [];
    try { detaylar = JSON.parse($row.attr('data-detay') || '[]'); } catch(e){ detaylar = []; }

    // Tablo oluştur
    let html = '<table id="detayTable" class="table table-sm table-bordered"><thead><tr>'+
        '<th>ID</th><th>Marka</th><th>Model</th><th>Açıklama</th><th>Başlangıç</th><th>Bitiş</th>'+
        '<th>Döngü</th><th>Miktar</th><th>Fiyat</th><th>İndirim</th><th>İndirim ER</th><th>Tutar</th><th>Fatura</th><th>Detay</th><th></th></tr></thead><tbody>';

    detaylar.forEach(d=>{
        const baslangic = formatDate(d.baslangic||'');
        const bitis = formatDate(d.bitis||'');
        const faturaYok = (d.fatura==0 || d.fatura==='0')?'selected':'';
        const faturaVar = (d.fatura==1 || d.fatura==='1')?'selected':'';

        let markaOptions = '<option value="">Seçiniz</option>';
        allMarkalar.forEach(m=> markaOptions += `<option value="${m.marka_id}" ${String(d.marka_id)==String(m.marka_id)?'selected':''}>${m.marka_ad}</option>`);

        let modelOptions = '<option value="">Önce marka seçiniz</option>';
        allModeller.filter(x=> String(x.marka_id) === String(d.marka_id)).forEach(m=>{
            modelOptions += `<option value="${m.model_id}" ${String(d.model_id)==String(m.model_id)?'selected':''}>${m.model_ad}</option>`;
        });

        html += `<tr data-id="${d.abone_hizmet_hareket_id||0}">
            <td><input type="text" class="form-control ID" value="${d.abone_hizmet_hareket_id||0}" readonly></td>
            <td><select class="form-control marka_select">${markaOptions}</select></td>
            <td><select class="form-control model_select">${modelOptions}</select></td>
            <td><input type="text" class="form-control aciklama" value="${d.aciklama||''}"></td>
            <td><input type="text" class="form-control baslangic datepicker" value="${baslangic}"></td>
            <td><input type="text" class="form-control bitis datepicker" value="${bitis}"></td>
            <td>
                <select class="form-control dongu">
                    <option value="">Seçiniz</option>
                    <option value="Aylık" ${d.dongu=='Aylık'?'selected':''}>Aylık</option>
                    <option value="Yıllık" ${d.dongu=='Yıllık'?'selected':''}>Yıllık</option>
                </select>
            </td>
            <td><input type="number" class="form-control miktar" value="${d.miktar || 0}"></td>
            <td><input type="text" class="form-control fiyat" value="${d.fiyat||0}"></td>

            <td>
              <input type="number" step="0.01"
                    class="form-control indirim"
                    value="${d.indirim || 0}">
            </td>

            <td>
              <input type="number" step="0.01"
                    class="form-control indirim_er"
                    value="${d.indirim_er || 0}">
            </td>

            <td>
              <input type="number"
                    class="form-control tutar"
                    value="${d.tutar||0}"
                    readonly>
            </td>

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

        const ozetHtml = `
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

    
    html += '</tbody></table>';
    html += ozetHtml;
    $('#modal-detay-body').html(html);
    initDatepicker($('#modal-detay-body'));
    hesaplaToplam();
});
 

    // Detay modalına satır ekleme
    $(document).on('click', '#detayAddRow', function (e) {
    e.preventDefault();

    const tbody = $('#detayTable tbody');
    if (!tbody.length) return;

    let markaOptions = '<option value="">Seçiniz</option>';
    allMarkalar.forEach(m =>
        markaOptions += `<option value="${m.marka_id}">${m.marka_ad}</option>`
    );

        const newRow = `<tr data-id="0">
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
            <td><input type="number" class="form-control miktar"></td>
            <td><input type="text" class="form-control fiyat"></td>

            <td>
              <input type="number" step="0.01"
                    class="form-control indirim"
                    value="0">
            </td>

            <td>
              <input type="number" step="0.01"
                    class="form-control indirim_er"
                    value="0">
            </td>

            <td>
              <input type="number"
                    class="form-control tutar"
                    readonly>
            </td>

            <td>
                <select class="form-control fatura">
                    <option value="0" selected>Yok</option>
                    <option value="1">Var</option>
                </select>
            </td>
            <td><input type="text" class="form-control detay"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        </tr>`;
        tbody.append(newRow);
    initDatepicker(tbody.find('tr:last'));
});

    // Satır silme (tek event)
    $(document).on('click', '.removeRow', function(){
        const tr = $(this).closest('tr');
        if(tr.closest('tbody').find('tr').length <= 1){
            alert('En az bir satır olmalı!');
            return;
        }
        const id = tr.data('id');
        if(id && id != 0){
            $.post('ajax_hareket_sil.php', {id:id}, function(res){
                if(res.success) tr.remove();
                else alert('Silme başarısız: '+res.error);
            }, 'json');
        } else tr.remove();
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



  $(document).on('click', '.btn-detay', function(){
        const $row = $(this).closest('tr');
        currentFirmaId = $row.data('firma') || $row.attr('data-firma') || null;
        currentAboneId = $(this).data('aboneid') || null;

        const fisTarihRaw = $row.data('fistarih') || $row.attr('data-fistarih') || '';
        const formatDate = s => {
            if(!s) return '';
            const p = String(s).split(/[-\/]/);
            if(p.length===3){
                if(p[0].length===4) return `${p[2]}/${p[1]}/${p[0]}`;
                if(p[2].length===4) return `${p[0]}/${p[1]}/${p[2]}`;
            }
            return s;
        };
        $('#modal-detay').data('fistarih', formatDate(fisTarihRaw));
    });

    $('#btnKaydetHareket').off('click').on('click', function(){
    let payload = [];
    let titleText = $('#modal-detay-title').text() || '';
    let firmaIdMatch = titleText.match(/\[ID:\s*(\d+)\]/);
    let firmaId = firmaIdMatch ? parseInt(firmaIdMatch[1]) : 0;

    $('#modal-detay-body table tbody tr').each(function(){
        const tr = $(this);
        const marka_id = tr.find('.marka_select').val();
        if(!marka_id) return;

        const abone_hizmet_id = parseInt(tr.data('id')) || null;


        payload.push({
            abone_hizmet_id: currentAboneId,
            marka_id: parseInt(marka_id) || 0,
            model_id: parseInt(tr.find('.model_select').val()) || 0,
            aciklama: tr.find('.aciklama').val(),
            baslangic: tr.find('.baslangic').val(),
            bitis: tr.find('.bitis').val(),
            dongu: tr.find('.dongu').val(),
            miktar: parseFloat(tr.find('.miktar').val()) || 0,
            fiyat: parseFloat(tr.find('.fiyat').val()) || 0,
            indirim: parseFloat(tr.find('.indirim').val()) || 0,
            indirim_er: parseFloat(tr.find('.indirim_er').val()) || 0,
            tutar: parseFloat(tr.find('.tutar').val()) || 0,
            fatura: parseInt(tr.find('.fatura').val()) || 0,
            detay: tr.find('.detay').val(),
            firma_id: firmaId,
            fis_tarih: $('#modal-detay').data('fistarih')
        });
    });

    if(payload.length === 0){
        alert('Kaydedilecek bir satır yok!');
        return;
    }



        $.when(
          $.post('ajax_teklif_ekle.php', {hareketler: payload}),
          $.post('ajax_aktarildi.php', {hareketler: payload})
      ).done(function(res1, res2){
          $('#modal-detay').modal('hide');
          location.reload();
      });
      });
      });


</script> 



</body>
</html>
