<?php include_once 'template/navbar2.php'; ?>
<?php include_once 'template/sidebar.php'; ?>


<?php
require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();

$modalOpen = false;
$foundData = [];

$adminclass = new AdminClass();

$kullanicilar = $adminclass->getAktifKullanicilar();


$admin->sadece_admin();


// Eğer destek_id GET ile geldiyse, düzenleme için veriyi al
if (isset($_GET['destek_id']) && !empty($_GET['destek_id'])) {
    $destek_id = (int)$_GET['destek_id'];
    $foundData = $admin->getDestekById($destek_id); // Bu fonksiyonu admin class'a yazman lazım
    $modalOpen = true;
}

// Destek listesi çek
$destekler = $admin->destek_Bilgi(); // Destekleri listeleyen fonksiyon
$firmalar = $adminclass->firma_Bilgi();
$subeler = $adminclass->getSubeBilgi();
$musteriler = $adminclass->getMusteriBilgi();



if (isset($_POST['destek_id_delete'])) {
    $destek_id = intval($_POST['destek_id_delete']);
    $result = $adminclass->deleteDestek($destek_id);

    if ($result) {
        header("Location: anasayfa.php?deleted=1");
        exit;
    } else {
        echo '<div class="alert alert-danger">Silme işlemi başarısız oldu!</div>';
      }

    }

?>


<div class="content-wrapper">
   <section class="content-header"><h1>Admin Destek</h1></section>
   <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-body">
          <table id="example1" class="table table-bordered table-striped">
                <thead>
                  
                  <tr>
                    <th>Firma AD</th>
                    <th>Şube AD</th>
                    <th>Müşteri AD</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Arıza</th>
                    <th>Yapılan İşlem</th>
                    <th>Sonuç</th>
                    <th>İşlemi Yapan Personel</th>
                    <th>Oluşturma Tarihi</th>
                    <th>Aktarılacak Personel</th>
                    <th>Planlanan Tarih</th>
                    <th>İşe Gidecek Personel</th>
                    <th>Not</th>                
                    <th>İşlemler</th>
                  </tr>
                </thead>
                <tbody>
                            <?php
                            $destekler = $adminclass->destek_Bilgi(); // Destek tablosundan veri çekme fonksiyonu örneği
                            if ($destekler) {
                            foreach ($destekler as $row) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['firma_ad'] ?? 'Bilgi Yok'); ?></td>
                                    <td><?php echo htmlspecialchars($row['sube_ad'] ?? 'Bilgi Yok'); ?></td>
                                    <td><?php echo htmlspecialchars($row['musteri_ad']); ?></td>
                                    <td><?php echo htmlspecialchars($row['eposta']); ?></td>
                                    <td><?php echo htmlspecialchars($row['telefon']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['ariza'])); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['yapilan_islem'])); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['sonuc'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['islemi_yapan_personel']); ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($row['olusturma_tarihi'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['aktarilacak_personel']); ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($row['planlanan_tarih'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['ise_gidecek_persone']); ?></td>
                                    <td><?php echo nl2br(htmlspecialchars($row['note'])); ?></td>

                                <td>
                                    <div class="d-flex" style="gap:5px;">

                                   <!-- Sil butonu -->
                                    <form method="POST" style="display:inline;" 
                                        onsubmit="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="destek_id_delete" value="<?= $row['destek_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                    </form>
                                    </div>
                                </td>
                                </tr>


<!-- Sil Modal -->
            <div class="modal fade" id="modal-delete-<?= $row['destek_id']; ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                <form method="POST">
                    <div class="modal-header"><h4 class="modal-title">Envanteri Sil</h4></div>
                    <div class="modal-body">
                    <p><strong><?= htmlspecialchars($row['ariza'] . ' ' . $row['yapilan_islem'] . ' ' . $row['sonuc']); ?></strong> kaydını silmek istediğinize emin misiniz?</p>
                    <input type="hidden" name="delete_destek" value="<?= $row['destek_id'];; ?>">
                    </div>
                    <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
                    <button class="btn btn-danger" type="submit">Sil</button>
                    </div>
                </form>
                </div>
            </div>
            </div>
<?php }}?>  
                </tbody>
                <tfoot>
                  <tr>
                    <th>Firma AD</th>
                    <th>Şube AD</th>
                    <th>Müşteri AD</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Arıza</th>
                    <th>Yapılan İşlem</th>
                    <th>Sonuç</th>
                    <th>İşlemi Yapan Personel</th>
                    <th>Oluşturma Tarihi</th>
                    <th>Aktarılacak Personel</th>
                    <th>Planlanan Tarih</th>
                    <th>İşe Gidecek Personel</th>
                    <th>Not</th>
                    
                    <th>İşlemler</th>
                  </tr>
                </tfoot>
              </table>
              
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

          <!-- /2. TABLO -->
          <div class="card mt-4">
  <div class="card-header">
    <h3 class="card-title">2. Tablo (Filtreleme Paneli)</h3>
  </div>
  <div class="card-body">

    <!-- Bu tablo sadece görsel, filtreleme butonu 1. tabloyu etkiliyor -->
    <table class="table table-bordered" style="width:100%;margin-top:20px;">
      <thead>
        <tr>
          <th>Filtre Paneli</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <!-- Sonuç Dropdown -->
            <select id="filterSonuc" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">Sonuç (Hepsi)</option>
              <option value="bitti">Bitti</option>
              <option value="devam ediyor">Devam Ediyor</option>
              <option value="aktarıldı">Aktarıldı</option>
              <option value="yerinde servis">Yerinde</option>
            </select>

            <!-- İşlemi Yapan -->
            <select id="filterIslemYapan" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">İşlemi Yapan (Hepsi)</option>
              <?php foreach($kullanicilar as $k): ?>
                <option value="<?= htmlspecialchars($k['isim']); ?>"><?= htmlspecialchars($k['isim']); ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Aktarılacak Personel -->
            <select id="filterAktarilacak" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">Aktarılacak (Hepsi)</option>
              <?php foreach($kullanicilar as $k): ?>
                <option value="<?= htmlspecialchars($k['isim']); ?>"><?= htmlspecialchars($k['isim']); ?></option>
              <?php endforeach; ?>
            </select>

            <!-- İşe Gidecek Personel -->
            <select id="filterGidecek" class="form-control d-inline-block" style="width:auto;display:inline-block;">
              <option value="">Gidecek (Hepsi)</option>
              <?php foreach($kullanicilar as $k): ?>
                <option value="<?= htmlspecialchars($k['isim']); ?>"><?= htmlspecialchars($k['isim']); ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Butonlar -->
            <button id="applyFilters" class="btn btn-primary ml-2">Filtrele</button>
            <button id="clearFilters" class="btn btn-secondary ml-2">Temizle</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>









<!-- Yeni Müşteri Modal -->
<div class="modal fade" id="modal-add-m-e">
  <div class="modal-dialog modal-lg"><!-- geniş modal için -->
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">Yeni Müşteri Ekle</h4>
        </div>
        <div class="modal-body">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label>Ad <span class="text-danger">*</span></label>
                <input type="text" name="musteri_ad" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label>Soyad</label>
                <input type="text" name="musteri_soyad" class="form-control">
              </div>

              <div class="col-md-6 mb-3">
                <label>Telefon</label>
                <input type="text" name="telefon" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
              </div>

              <div class="col-md-6 mb-3">
                <label>Firma Seç <span class="text-danger">*</span></label>
                <select name="firma_id" id="firma_select" class="form-control" required>
                  <option value="">Firma Seçiniz</option>
                  <?php foreach ($firmalar as $f): ?>
                    <option value="<?= $f['firma_id']; ?>"><?= htmlspecialchars($f['firma_ad']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label>Şube Seç <span class="text-danger">*</span></label>
                <select name="sube_id" id="sube_select" class="form-control" required>
                  <option value="">Önce firma seçiniz</option>
                  <!-- JS ile dolacak -->
                </select>
              </div>

              <div class="col-md-6 mb-3">
                <label>Cihaz Türü <span class="text-danger">*</span></label>
                <input type="text" name="cihaz_turu" value="" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label>Marka</label>
                <input type="text" name="marka" value="" class="form-control">
              </div>

              <div class="col-md-4 mb-3">
                <label>Model</label>
                <input type="text" name="model" value="" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>İşlemci</label>
                <input type="text" name="islemci" value="" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Bellek</label>
                <input type="text" name="bellek" value="" class="form-control">
              </div>

              <div class="col-md-6 mb-3">
                <label>Disk</label>
                <input type="text" name="disk"value="" class="form-control">
              </div>
              <div class="col-md-6 mb-3">
                <label>İşletim Sistemi</label>
                <input type="text" name="isletim_sistemi" value="" class="form-control">
              </div>

              <div class="col-12 mb-3">
                <label>Yüklü Uygulamalar</label>
                <textarea name="uygulamalar" class="form-control" rows="3"></textarea>
              </div>

              <div class="col-12 mb-3">
                <label>Ek Bilgi</label>
                <textarea name="bilgi" class="form-control" rows="3"></textarea>
              </div>
            </div>
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




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function(){
      if (!$('body').hasClass('sidebar-collapse')) {
        $('body').addClass('sidebar-collapse');
      }
    });
  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>

<script>
document.getElementById("musteri_ara_btn").addEventListener("click", function () {
    const query = document.getElementById("musteri_ara_input").value.trim();
    if (!query) {
        alert("Lütfen bir arama terimi girin!");
        return;
    }

    fetch("musteri_ara.php?q=" + encodeURIComponent(query))
        .then(res => res.json())
        .then(data => {
            console.log("📥 JSON DATA:", data);

            let sonucDiv = document.getElementById("musteri_sonuc");
            let musteriBilgileriDiv = document.getElementById("musteri_bilgileri");

            if (data.success && data.results.length > 0) {
                let html = "<ul class='list-group'>";

                data.results.forEach(musteri => {
                    // JSON -> güvenli string
                    let musteriJSON = encodeURIComponent(JSON.stringify(musteri));

                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${musteri.musteri_ad} ${musteri.musteri_soyad}</strong><br>
                                Firma: ${musteri.firma_ad} | Şube: ${musteri.sube_ad}<br>
                                Tel: ${musteri.telefon} | Email: ${musteri.email}<br>
                                <span class="badge badge-info">Hizmet Türü: ${musteri.tur_adlar ?? '-'}</span><br>
                                <span class="badge badge-${musteri.hizmet_durum === 'Aktif' ? 'success' : 'secondary'}">
                                    Durum: ${musteri.hizmet_durum}
                                </span>

                            </div>
                            <button type="button" class="btn btn-sm btn-success musteri-sec-btn" data-json="${musteriJSON}">
                                Bu veriyi kullan
                            </button>
                        </li>`;
                });

                html += "</ul>";

                musteriBilgileriDiv.innerHTML = html;
                sonucDiv.style.display = "block";

            } else {
                sonucDiv.style.display = "block";
                musteriBilgileriDiv.innerHTML =
                    `<p style="color:red;">Müşteri bulunamadı!</p>`;
            }
        })
        .catch(err => {
            console.error("❌ FETCH HATASI:", err);
            alert("Bir hata oluştu! Konsolu kontrol et.");
        });
});

// Modal form alanlarına dolduran fonksiyon (global tanımla)
function musteriSec(musteri) {
    document.querySelector("input[name='firma_adi']").value = musteri.firma_ad ?? '';
    document.querySelector("input[name='firma_idi']").value  = musteri.firma_id ?? '';
    document.querySelector("input[name='sube_adi']").value       = musteri.sube_ad ?? '';
    document.querySelector("input[name='sube_idi']").value  = musteri.sube_id ?? '';
    document.querySelector("input[name='musteri_adi']").value    = musteri.musteri_ad ?? '';
    document.querySelector("input[name='musteri_soyadi']").value = musteri.musteri_soyad ?? '';
    document.querySelector("input[name='epostai']").value        = musteri.email ?? '';
    document.querySelector("input[name='telefoni']").value       = musteri.telefon ?? '';
    document.querySelector("input[name='hizmet_durumi']").value  = musteri.hizmet_durum ?? '';
    document.querySelector("input[name='tur_adi']").value        = musteri.tur_adlar ?? '';  // Burada tur_adlar kullanıldı
    document.querySelector("input[name='cihaz_turui']").value     = musteri.cihaz_turu ?? '';
    document.querySelector("input[name='markai']").value          = musteri.marka ?? '';
    document.querySelector("input[name='modeli']").value          = musteri.model ?? '';
    document.querySelector("input[name='islemcii']").value        = musteri.islemci ?? '';
    document.querySelector("input[name='belleki']").value         = musteri.bellek ?? '';
    document.querySelector("input[name='diski']").value           = musteri.disk ?? '';
    document.querySelector("input[name='isletim_sistemii']").value= musteri.isletim_sistemi ?? '';
    document.querySelector("input[name='uygulamalari']").value    = musteri.uygulamalar ?? '';
    document.querySelector("input[name='bilgii']").value          = musteri.bilgi ?? '';
    if (document.querySelector("input[name='musteri_idi']"))
        document.querySelector("input[name='musteri_idi']").value = musteri.musteri_id ?? '';
    if (document.querySelector("textarea[name='ariza']"))
         document.querySelector("textarea[name='ariza']").value = musteri.ariza ?? '';

    if (document.querySelector("textarea[name='yapilan_islem']"))
        document.querySelector("textarea[name='yapilan_islem']").value = musteri.yapilan_islem ?? '';

    if (document.querySelector("select[name='sonuc']"))
        document.querySelector("select[name='sonuc']").value = musteri.sonuc ?? '';

    if (document.querySelector("select[name='islemi_yapan_personel']"))
        document.querySelector("select[name='islemi_yapan_personel']").value = musteri.islemi_yapan_personel ?? '';

    if (document.querySelector("textarea[name='note']"))
        document.querySelector("textarea[name='note']").value = musteri.note ?? '';

}

// Buton click eventini dinle
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("musteri-sec-btn")) {
        let musteri = JSON.parse(decodeURIComponent(e.target.dataset.json));

        musteriSec(musteri);
        // Seçim yapıldıktan sonra sonucu gizleyelim
        document.getElementById("musteri_sonuc").style.display = "none";
    }
});


  // PHP'den gelen tüm şubeleri JS objesine aktaralım
  const allSubeler = <?= json_encode($subeler); ?>;

  // Yeni müşteri modalındaki firma seçildiğinde şubeleri filtrele
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

  // Güncelle modalındaki firma seçimi değiştiğinde ilgili şubeleri doldur
  document.querySelectorAll('.firma-select-edit').forEach(firmaSelect => {
    const musteriId = firmaSelect.getAttribute('data-musteri-id');
    const subeSelect = document.querySelector('.sube-select-edit-' + musteriId);

    function doldur(selectedFirmaId, selectedSubeId = null) {
      subeSelect.innerHTML = '';
      if (!selectedFirmaId) {
        subeSelect.innerHTML = '<option value="">Önce firma seçiniz</option>';
        return;
      }
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

    // İlk yüklemede doğru şubeyi seç
    const başlangıçFirmaId = firmaSelect.value;
    const başlangıçSubeId = <?= json_encode($musteriler); ?>.find(m => m.musteri_id == musteriId).sube_id;
    doldur(başlangıçFirmaId, başlangıçSubeId);

    // Firma seçimi değişince şubeleri güncelle
    firmaSelect.addEventListener('change', function() {
      doldur(this.value);
    });
  });

// Filtreleme Butonu
const applyFiltersBtn = document.getElementById("applyFilters");
if (applyFiltersBtn) {
  applyFiltersBtn.addEventListener("click", () => {
    const sonucFilter = document.getElementById("filterSonuc")?.value.toLowerCase().trim() || "";
    const islemFilter = document.getElementById("filterIslemYapan")?.value.toLowerCase().trim() || "";
    const aktarilacakFilter = document.getElementById("filterAktarilacak")?.value.toLowerCase().trim() || "";
    const gidecekFilter = document.getElementById("filterGidecek")?.value.toLowerCase().trim() || "";

    document.querySelectorAll("#example1 tbody tr").forEach(row => {
      const sonuc = row.cells[8]?.textContent.toLowerCase().trim() || "";
      const islemYapan = row.cells[9]?.textContent.toLowerCase().trim() || "";
      const aktarilacak = row.cells[11]?.textContent.toLowerCase().trim() || "";
      const gidecek = row.cells[13]?.textContent.toLowerCase().trim() || "";

      const matchSonuc = !sonucFilter || sonuc.includes(sonucFilter);
      const matchIslem = !islemFilter || islemYapan.includes(islemFilter);
      const matchAktar = !aktarilacakFilter || aktarilacak.includes(aktarilacakFilter);
      const matchGidecek = !gidecekFilter || gidecek.includes(gidecekFilter);

      row.style.display = (matchSonuc && matchIslem && matchAktar && matchGidecek) ? "" : "none";
    });
  });
}

// Temizleme Butonu
const clearFiltersBtn = document.getElementById("clearFilters");
if (clearFiltersBtn) {
  clearFiltersBtn.addEventListener("click", () => {
    ["filterSonuc", "filterIslemYapan", "filterAktarilacak", "filterGidecek"].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.value = "";
    });

    document.querySelectorAll("#example1 tbody tr").forEach(row => {
      row.style.display = "";
    });
  });
}   


</script>


</body>
</html>   