<?php include_once 'template/navbar.php'; ?>
<style>
.content-wrapper {
  margin-left: 0 !important;
  padding-left: 15px;
}
.alfabe-btn:hover {
  background-color: #343a40 !important;
  color: #fff !important;
}
/* Alfabe tablosu için özel tasarım */
.alfabe-card {
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  border-top: 3px solid #343a40;
}
</style>

<?php if (!empty($mesaj)): ?>
  <div class="alert alert-info"><?= $mesaj; ?></div>
<?php endif; ?>

<?php
// AdminClass dahil ve örneği
$adminclass = new AdminClass();

$firmalar = $adminclass->firma_Bilgi();
$subeler = $adminclass->getSubeBilgi();

$alfabe = ['A','B','C','Ç','D','E','F','G','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z','TÜMÜ'];

$mesaj = ""; 

if (isset($_POST['save']) && $_POST['save'] == 3001) {
    $ad = $adminclass->getSecurity($_POST['musteri_ad']);
    $soyad = $adminclass->getSecurity($_POST['musteri_soyad']);
    $telefon = $adminclass->getSecurity($_POST['telefon']);
    $email = $adminclass->getSecurity($_POST['email']);
    $sube_id = intval($_POST['sube_id']);

    $sql = "INSERT INTO musteri (musteri_ad, musteri_soyad, telefon, email, sube_id) VALUES (?, ?, ?, ?, ?)";
    $mesaj = $adminclass->pdoInsert($sql, [$ad, $soyad, $telefon, $email, $sube_id]);

    header("Location: musteriler.php");
    exit();
}

if (isset($_POST['update']) && $_POST['update'] == 3002) {
    $musteri_id = intval($_POST['musteri_id']);
    $ad = $adminclass->getSecurity($_POST['musteri_ad']);
    $soyad = $adminclass->getSecurity($_POST['musteri_soyad']);
    $telefon = $adminclass->getSecurity($_POST['telefon']);
    $email = $adminclass->getSecurity($_POST['email']);
    $sube_id = intval($_POST['sube_id']);

    $sql = "UPDATE musteri SET musteri_ad = ?, musteri_soyad = ?, telefon = ?, email = ?, sube_id = ? WHERE musteri_id = ?";
    $adminclass->pdoPrepare($sql, [$ad, $soyad, $telefon, $email, $sube_id, $musteri_id]);
    header("Location: musteriler.php");
    exit();
}

if (isset($_POST['musteri_id_delete'])) {
    $delete_id = intval($_POST['musteri_id_delete']);
    $sql = "DELETE FROM musteri WHERE musteri_id = ?";
    $adminclass->pdoDelete($sql, [$delete_id]);
    header("Location: musteriler.php");
    exit();
}
?>

<div class="content-wrapper">
   <section class="content-header"><h1>Müşteriler</h1></section>
   <section class="content">
    <div class="container-fluid">
      
      <div class="card mb-4 alfabe-card">
        <div class="card-body p-3">
          <table class="table table-borderless table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-center">
                  <h5 class="mb-3 text-secondary"><i class="fas fa-filter"></i> Alfabetik Filtreleme</h5>
                  <div class="d-flex justify-content-center flex-wrap" id="alfabe-container">
                    <?php foreach ($alfabe as $harf): ?>
                        <button type="button" class="btn btn-sm bg-light text-dark alfabe-btn mb-1 mr-1 font-weight-bold" data-secim="<?= $harf ?>" style="border: 2px solid black; min-width: 38px;">
                            <?= $harf ?>
                        </button>
                    <?php endforeach; ?>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
            <button class="btn btn-success" data-toggle="modal" data-target="#modal-add-e">Yeni Ekle</button>
        </div>
        <div class="card-body">
          <table id="example1" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Ad Soyad</th>
                <th>Telefon</th>
                <th>Email</th>
                <th>Firma</th>
                <th>Şube</th>
                <th>İşlem</th>
              </tr>
            </thead>
            <tbody id="ajax-musteri-tablo-body">
              <?php 
                $secili_harf = isset($_POST['harf']) ? mb_strtoupper($_POST['harf'], 'UTF-8') : 'A';
                $musteriler = $adminclass->getMusteriBilgi_Filtreli($secili_harf);

                if ($musteriler) {
                  foreach ($musteriler as $m): 
              ?>
                <tr>
                  <td><?= $m['musteri_id']; ?></td>
                  <td><?= htmlspecialchars($m['musteri_ad'] . ' ' . $m['musteri_soyad']); ?></td>
                  <td><?= htmlspecialchars($m['telefon']); ?></td>
                  <td><?= htmlspecialchars($m['email']); ?></td>
                  <td><?= htmlspecialchars($m['firma_ad']); ?></td>
                  <td><?= htmlspecialchars($m['sube_ad']); ?></td>
                  <td>
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-edit-<?= $m['musteri_id']; ?>">Güncelle</button>
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modal-delete-<?= $m['musteri_id']; ?>">Sil</button>

                    <div class="modal fade" id="modal-edit-<?= $m['musteri_id']; ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST">
                            <div class="modal-header"><h4 class="modal-title">Müşteri Güncelle</h4></div>
                            <div class="modal-body">
                              <div class="form-group">
                                <label>Ad</label>
                                <input type="text" name="musteri_ad" value="<?= htmlspecialchars($m['musteri_ad']); ?>" class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Soyad</label>
                                <input type="text" name="musteri_soyad" value="<?= htmlspecialchars($m['musteri_soyad']); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Telefon</label>
                                <input type="text" name="telefon" value="<?= htmlspecialchars($m['telefon']); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($m['email']); ?>" class="form-control">
                              </div>
                              <div class="form-group">
                                <label>Firma</label>
                                <select class="form-control firma-select-edit" data-musteri-id="<?= $m['musteri_id']; ?>" data-current-sube-id="<?= $m['sube_id']; ?>" required>
                                  <option value="">Firma Seçiniz</option>
                                  <?php foreach ($firmalar as $f): ?>
                                    <option value="<?= $f['firma_id']; ?>" <?= ($f['firma_id'] == $m['firma_id']) ? 'selected' : ''; ?>>
                                      <?= htmlspecialchars($f['firma_ad']); ?>
                                    </option>
                                  <?php endforeach; ?>
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Şube</label>
                                <select name="sube_id" class="form-control sube-select-edit-<?= $m['musteri_id']; ?>" required></select>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <input type="hidden" name="musteri_id" value="<?= $m['musteri_id']; ?>">
                              <input type="hidden" name="update" value="3002">
                              <button class="btn btn-primary" type="submit">Kaydet</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <div class="modal fade" id="modal-delete-<?= $m['musteri_id']; ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="POST">
                            <div class="modal-header"><h4 class="modal-title">Müşteri Sil</h4></div>
                            <div class="modal-body">
                              <p><strong><?= htmlspecialchars($m['musteri_ad'] . ' ' . $m['musteri_soyad']); ?></strong> adlı müşteriyi silmek istediğinize emin misiniz?</p>
                              <input type="hidden" name="musteri_id_delete" value="<?= $m['musteri_id']; ?>">
                            </div>
                            <div class="modal-footer">
                              <button class="btn btn-secondary" type="button" data-dismiss="modal">Vazgeç</button>
                              <button class="btn btn-danger" type="submit">Sil</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php 
                  endforeach; 
                } 
              ?>
            </tbody>
            <tfoot>
              <tr>
                <th>ID</th>
                <th>Ad Soyad</th>
                <th>Telefon</th>
                <th>Email</th>
                <th>Firma</th>
                <th>Şube</th>
                <th>İşlem</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<div class="modal fade" id="modal-add-e">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" >
        <div class="modal-header"><h4 class="modal-title">Yeni Müşteri Ekle</h4></div>
        <div class="modal-body">
          <div class="form-group">
            <label>Ad</label>
            <input type="text" name="musteri_ad" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Soyad</label>
            <input type="text" name="musteri_soyad" class="form-control">
          </div>
          <div class="form-group">
            <label>Telefon</label>
            <input type="text" name="telefon" class="form-control">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="form-group">
            <label>Firma Seç</label>
            <select name="firma_id" id="firma_select" class="form-control" required>
              <option value="">Firma Seçiniz</option>
              <?php foreach ($firmalar as $f): ?>
                <option value="<?= $f['firma_id']; ?>"><?= htmlspecialchars($f['firma_ad']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Şube Seç</label>
            <select name="sube_id" id="sube_select" class="form-control" required>
              <option value="">Önce firma seçiniz</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Kapat</button>
          <button type="submit" name="save" value="3001" class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// TÜM ŞUBELERİ JSON OLARAK JS'YE AL
const allSubeler = <?= json_encode($subeler); ?>;

// 1. YENİ EKLE MODALI İÇİN ŞUBE FİLTRESİ
document.getElementById('firma_select').addEventListener('change', function() {
    const firmaId = this.value;
    const subeSelect = document.getElementById('sube_select');
    subeSelect.innerHTML = '<option value="">Şube Seçiniz</option>';

    if(firmaId) {
        const filteredSubeler = allSubeler.filter(s => s.firma_id == firmaId);
        filteredSubeler.forEach(sube => {
            const option = document.createElement('option');
            option.value = sube.sube_id;
            option.textContent = sube.sube_ad;
            subeSelect.appendChild(option);
        });
    }
});

// 2. GÜNCELLE MODALLARI İÇİN DİNAMİK ŞUBE DOLDURMA
function bindDynamicEvents() {
    document.querySelectorAll('.firma-select-edit').forEach(firmaSelect => {
        const musteriId = firmaSelect.getAttribute('data-musteri-id');
        const subeSelect = document.querySelector('.sube-select-edit-' + musteriId);
        
        function doldur(selectedFirmaId, selectedSubeId = null) {
            subeSelect.innerHTML = '<option value="">Önce firma seçiniz</option>';
            if (!selectedFirmaId) return;
            
            const filteredSubeler = allSubeler.filter(s => s.firma_id == selectedFirmaId);
            filteredSubeler.forEach(sube => {
                const option = document.createElement('option');
                option.value = sube.sube_id;
                option.textContent = sube.sube_ad;
                if (selectedSubeId && sube.sube_id == selectedSubeId) {
                    option.selected = true;
                }
                subeSelect.appendChild(option);
            });
        }

        const baslangicFirmaId = firmaSelect.value;
        const baslangicSubeId = firmaSelect.getAttribute('data-current-sube-id');
        doldur(baslangicFirmaId, baslangicSubeId);

        firmaSelect.addEventListener('change', function() {
            doldur(this.value);
        });
    });
}

// ========================================================
// SİHİRLİ AJAX: DATATABLES'I BOZMAYAN KESİN ÇÖZÜM
// ========================================================
document.addEventListener("DOMContentLoaded", function() {
    
    // Olayları ilk yüklemede bağla
    bindDynamicEvents();

    const butonlar = document.querySelectorAll('.alfabe-btn');

    function ajaxIleGetir(harf, tiklananButon) {
        
        butonlar.forEach(btn => {
            btn.classList.remove('bg-dark', 'text-white');
            btn.classList.add('bg-light', 'text-dark');
        });

        if(tiklananButon) {
            tiklananButon.classList.remove('bg-light', 'text-dark');
            tiklananButon.classList.add('bg-dark', 'text-white');
        }

        let formData = new FormData();
        formData.append('harf', harf);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text()) 
        .then(html => {
            // HTML'i DOMParser ile ayrıştır
            let parser = new DOMParser();
            let doc = parser.parseFromString(html, 'text/html');
            
            // Gelen sayfadaki sadece yeni satırları (TR) bul
            let yeniSatirlar = doc.querySelectorAll('#ajax-musteri-tablo-body tr');
            
            // FOOTER'DAKİ DATATABLES'A BAĞLAN (Asla destroy yapmıyoruz!)
            let dt = $('#example1').DataTable();
            
            // Tablonun içini temizle (Bu işlem DataTables hafızasını temizler)
            dt.clear();
            
            // Yeni gelen HTML satırlarını DataTables'a ekle
            yeniSatirlar.forEach(satir => {
                dt.row.add(satir);
            });
            
            // Tabloyu ekranda güncelle (Arama, sayfalama ve o güzel tasarımın sabit kalır)
            dt.draw();
            
            // Modalların firma/şube olaylarını tekrar canlandır
            bindDynamicEvents();
        })
        .catch(error => {
            console.error('AJAX Hatası:', error);
        });
    }

    butonlar.forEach(btn => {
        btn.addEventListener('click', function() {
            const secilenHarf = this.getAttribute('data-secim');
            ajaxIleGetir(secilenHarf, this);
        });
    });

    const varsayilanButon = document.querySelector('.alfabe-btn[data-secim="A"]');
    if(varsayilanButon) {
        varsayilanButon.classList.remove('bg-light', 'text-dark');
        varsayilanButon.classList.add('bg-dark', 'text-white');
    }
});
</script>