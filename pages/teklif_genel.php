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



// Abone hizmetleri ve hareketlerini çek
$hareketler = $admin->getGenelTeklifler();  // AdminClass'ta tüm kayıtları döndüren fonksiyon olmalı
$firmalar = $adminclass->pdoQuery("SELECT * FROM firma ORDER BY firma_ad");
$markalar = $adminclass->pdoQuery("SELECT * FROM marka ORDER BY marka_ad");
$modeller = $adminclass->pdoQuery("SELECT * FROM model ORDER BY model_ad");
?>

<div class="content-wrapper">
  <section class="content-header"><h1>Bütün Teklifler</h1></section>
  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <!-- Sola yaslı -->
                <!-- Ortalanmış -->
        <div class="mx-auto">
          <a href="teklif_ekleme.php" class="btn btn-outline-dark mr-2">Teklif Ver</a>
          <a href="teklif_bekleyen.php" class="btn btn-outline-dark mr-2">Bekleyen Teklifler</a>
          <a href="teklif_genel.php" class="btn btn-outline-dark">Bütün Teklifler</a>
</div>
          </div>
        <div class="card-body">
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
                <th>Durum</th>
                <th>İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($hareketler as $row): ?>
              <tr 
                  data-detay='<?= htmlspecialchars(json_encode($admin->getBekleyenTeklifHareketler($row['teklif_id'])), ENT_QUOTES, 'UTF-8') ?>'
                  data-firma="<?= htmlspecialchars($row['firma_ad'], ENT_QUOTES, 'UTF-8') ?>"
                  data-fistarih="<?= htmlspecialchars($row['fis_tarih'], ENT_QUOTES, 'UTF-8') ?>"
              >
                  <td><?= htmlspecialchars($row['teklif_id']) ?></td>
                  <td><?= htmlspecialchars($row['firma_ad']) ?></td>
                  <td><?= htmlspecialchars($row['marka_ad']) ?></td>
                  <td><?= htmlspecialchars($row['model_ad']) ?></td>
                  <td><?= htmlspecialchars($row['aciklama']) ?></td>
                  <td><?= $row['bitis'] ? date('d/m/Y', strtotime($row['bitis'])) : '' ?></td>
                  <td><?= number_format($row['tutar'] ?? 0, 2, ',', '.') ?> ₺</td>
                  <td><?= htmlspecialchars($row['durum']) ?></td>
                  <td>
                      <button class="btn btn-info btn-sm btn-detay" data-toggle="modal" data-target="#modal-detay"
    data-firma="<?= htmlspecialchars($row['firma_ad'], ENT_QUOTES) ?>"  
    data-detay='<?= htmlspecialchars(json_encode($admin->getBekleyenTeklifHareketler($row['teklif_id'])), ENT_QUOTES) ?>'  
    data-aboneid="<?= $row['teklif_id'] ?>"
>Detay</button>



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
        <!-- PDF butonunu dinamik yapıyoruz -->
         <a href="#" target="_blank" class="btn  btn-warning" id="btnPDF_u">
          <i class="fa fa-file-pdf-o"></i> $USD
        </a>
        <a href="#" target="_blank" class="btn  btn-warning" id="btnPDF">
          <i class="fa fa-file-pdf-o"></i> Normal
        </a>
         <a href="#" target="_blank" class="btn btn-warning" id="btnPDF_i">
          <i class="fa fa-file-pdf-o"></i> İndirimli
        </a>
        <a href="#" target="_blank" class="btn btn-warning" id="btnPDF_d">
          <i class="fa fa-file-pdf-o"></i> Domain
        </a>
        <a href="#" target="_blank" class="btn btn-warning" id="btnPDF_k">
          <i class="fa fa-file-pdf-o"></i> Kampanya
        </a>
        <a href="#" target="_blank" class="btn btn-primary" id="btnPDF_e">
          <i class="fa fa-file-pdf-o"></i> ETA
        </a>
        <a href="#" target="_blank" class="btn btn-primary" id="btnPDF_es">
          <i class="fa fa-file-pdf-o"></i> ETA İnd
        </a>
        <a href="#" target="_blank" class="btn btn-primary" id="btnPDF_ek">
          <i class="fa fa-file-pdf-o"></i> ETA Kmpy.
        </a>
        <a href="#" target="_blank" class="btn btn-danger" id="btnPDF_l">
          <i class="fa fa-file-pdf-o"></i> Logo İnd.
        </a>
        <a href="#" target="_blank" class="btn btn-danger" id="btnPDF_ls">
          <i class="fa fa-file-pdf-o"></i> Logo Sdkt.
        </a>
        <button type="button" id="btnKaydetHareket" class="btn btn-success">Kaydet</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
      </div>
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
    let currentAboneId = null;

    // Datepicker başlatma fonksiyonu
    function initDatepicker(row){
        row.find('.datepicker').datepicker({
            dateFormat: "dd/mm/yy",
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true
        });
    }

    // Ana tabloya satır ekleme
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

    // Satır silme (ana tablo ve modal tablo için tek event)
    $(document).on('click', '.removeRow', function(){
        const tr = $(this).closest('tr');
        const tbody = tr.closest('tbody');

        if(tbody.find('tr').length <= 1){
            alert('En az bir satır olmalı!');
            return;
        }

        const id = tr.data('id');

        if(id && id != 0){
            $.post('ajax_hareket_sil.php', {id:id}, function(res){
                if(res.success){
                    tr.remove();
                } else {
                    alert('Silme başarısız: ' + res.error);
                }
            }, 'json');
        } else {
            tr.remove();
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

// Miktar × Fiyat → Tutar hesaplama (iki aşamalı indirim)
$(document).on('input', '.miktar, .fiyat, .indirim, .indirim_er', function(){
    const row = $(this).closest('tr');

    const miktar = parseFloat(row.find('.miktar').val()) || 0;
    const fiyat = parseFloat(row.find('.fiyat').val()) || 0;
    const indirim = parseFloat(row.find('.indirim').val()) || 0;
    const indirim_er = parseFloat(row.find('.indirim_er').val()) || 0;

    let tutar = miktar * fiyat;

    if(indirim > 0) {
        tutar -= (tutar * indirim / 100);
    }

    if(indirim_er > 0) {
        tutar -= (tutar * indirim_er / 100);
    }

    row.find('.tutar').val(tutar.toFixed(2));

    // 🔥 ALT TABLOYU DA ANLIK GÜNCELLE
    hesaplaToplam();
});



    // Tekil modal açılınca verileri doldur
    $(document).on('click', '.btn-detay', function(){
        const $row = $(this).closest('tr');

        const currentFirmaAd = $row.data('firma') || '';
        const currentFirmaId = $row.data('firma-id') || null;
        currentAboneId = $(this).data('aboneid') || null;

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
        

        $('#modal-detay-title').text(`Hizmet Detayları - ${currentFirmaAd} [ID: ${currentFirmaId}]${fisTarih ? ' (Fiş: '+fisTarih+')' : ''}`);
        $('#modal-detay').data('fistarih', fisTarih);
        $('#modal-detay').data('firma-id', currentFirmaId);

        

        let detaylar = [];
        try { detaylar = JSON.parse($row.attr('data-detay') || '[]'); } catch(e){ detaylar = []; }

        let html = '<table id="detayTable" class="table table-sm table-bordered"><thead><tr>'+
                    '<th>ID</th><th>Marka</th><th>Model</th><th>Açıklama</th><th>Başlangıç</th><th>Bitiş</th>'+
                    '<th>Döngü</th><th>Miktar</th><th>Fiyat</th><th>İndirim</th><th>İndirim_er</th><th>Tutar</th><th>Fatura</th><th>Detay</th><th></th></tr></thead><tbody>';


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

            html += `<tr data-id="${d.teklif_hareket_id||0}">
                <td><input type="text" class="form-control ID" value="${d.teklif_hareket_id||0}" readonly></td>
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
                <td><input type="number" class="form-control miktar" value="${d.miktar||0}"></td>
                <td><input type="text" class="form-control fiyat" value="${d.fiyat||0}"></td>
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
                <td><input type="number" class="form-control tutar" value="${d.tutar||0}" readonly></td>
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
        hesaplaToplam();
        initDatepicker($('#modal-detay-body'));
        $('#btnPDF').attr('href', 'pdf.php?q=' + currentAboneId);
        $('#btnPDF_u').attr('href', 'pdfDolar.php?q=' + currentAboneId);
        $('#btnPDF_i').attr('href', 'pdf_indirim.php?q=' + currentAboneId);
        $('#btnPDF_d').attr('href', 'pdf_domain.php?q=' + currentAboneId);
        $('#btnPDF_k').attr('href', 'pdf_indirim_kampanya?q=' + currentAboneId);
        $('#btnPDF_l').attr('href', 'pdf_indirim_logo.php?q=' + currentAboneId);
        $('#btnPDF_ls').attr('href', 'pdf_indirim_logo_sdkt.php?q=' + currentAboneId);
        $('#btnPDF_e').attr('href', 'pdf_eta.php?q=' + currentAboneId);
        $('#btnPDF_es').attr('href', 'pdf_indirim_eta_sdkt.php?q=' + currentAboneId);
        $('#btnPDF_ek').attr('href', 'pdf_eta_kampanya.php?q=' + currentAboneId);
    });

    // Modal tabloya satır ekleme
    $(document).on('click', '#detayAddRow', function(){
        const tbody = $('#detayTable tbody');
        if(!tbody.length) return;

        let markaOptions = '<option value="">Seçiniz</option>';
        allMarkalar.forEach(m=> markaOptions += `<option value="${m.marka_id}">${m.marka_ad}</option>`);

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
        </tr>`;
        tbody.append(newRow);
        initDatepicker(tbody.find('tr:last'));
    });

    // Modal içindeki satır silme
$(document).on('click', '#modal-detay-body .removeRow', function(){
    const tr = $(this).closest('tr');
    const tbody = tr.closest('tbody');

    // Eğer tek satır kaldıysa uyar
    if(tbody.find('tr').length <= 1){
        alert('En az bir satır olmalı!');
        return;
    }

    // Satırı direkt frontend'den kaldır
    tr.remove();
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


$(document).ready(function(){

    // Kaydet butonu
    $('#btnKaydetHareket').off('click').on('click', function(){
        let payload = [];
        let titleText = $('#modal-detay-title').text() || '';
        let firmaIdMatch = titleText.match(/\[ID:\s*(\d+)\]/);
        let firmaId = firmaIdMatch ? parseInt(firmaIdMatch[1]) : 0;

        $('#modal-detay-body table tbody tr').each(function(){
            const tr = $(this);
            const marka_id = tr.find('.marka_select').val();
            if(!marka_id) return;

            payload.push({
                teklif_hareket_id: tr.data('id') || 0,
                teklif_id: currentAboneId,
                marka_id: parseInt(marka_id) || 0,
                model_id: parseInt(tr.find('.model_select').val()) || 0,
                aciklama: tr.find('.aciklama').val(),
                baslangic: tr.find('.baslangic').val(),
                bitis: tr.find('.bitis').val(),
                dongu: tr.find('.dongu').val(),
                miktar: parseFloat(tr.find('.miktar').val()) || 0,
                fiyat: parseFloat(tr.find('.fiyat').val()) || 0,
                indirim: parseInt(tr.find('.indirim').val()) || 0,
                indirim_er: parseInt(tr.find('.indirim_er').val()) || 0,
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

        $.post('ajax_teklif_bekleyen_hareket.php', {hareketler: payload}).done(function(){
            $('#modal-detay').modal('hide');
            window.location.href = 'teklif_bekleyen.php?openModal=' + currentAboneId;
        });

        $('#modal-detay').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });
    });

    // Sayfa yüklendiğinde URL'den openModal parametresi varsa modalı aç
    const urlParams = new URLSearchParams(window.location.search);
    const openModalId = urlParams.get('openModal');
    if(openModalId){
        const $btn = $(`.btn-detay[data-aboneid="${openModalId}"]`);
        if($btn.length){
            $btn.trigger('click');
        }
    }

});

$(document).on('click', '.btn-kabul', function(){
    const teklifId = $(this).data('id');

    if(confirm("⚠️ Bu teklifi Aktarıp Kabul Etmek üzeresiniz.")){
        $.post('ajax_abone_ekle.php', {teklif_id: teklifId}, function(res){
            console.log("Sonuç:", res);

            if(res.success){
                alert("✅ " + res.message);
            } else {
                alert("❌ İşlem başarısız: " + (res.error || ""));
            }
        }, 'json').always(function(){
            // ✅ Başarılı ya da hatalı fark etmez, her durumda yenile
            location.reload();
        });
    }
});




// Ret butonu
$(document).on('click', '.btn-ret', function(){
    const teklifId = $(this).data('id');
    if(confirm("⚠️ Bu teklifi Ret olarak güncellemek üzeresiniz.")){
        $.post('ajax_teklif_durum.php', {teklif_id: teklifId, durum: 'Ret'}, function(response){
            console.log('Ret cevabı:', response);
            window.location.href = 'teklif_bekleyen.php';
        });
    }
});






        // Satır silme ve DB'den silme
$(document).on('click', '.removeRow', function(){
    const tr = $(this).closest('tr');
    const id = tr.data('id');

    // En az bir satır olmalı
    if($('#modal-detay-body tr').length <= 1){
        alert('En az bir satır olmalı!');
        return;
    }

           if(id && id != 0){
        // DB'de kayıtlı, AJAX ile sil
        $.post('ajax_bekleyen_hareket_sil.php', {id: id}, function(res){
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

});


</script>




</body>
</html>
