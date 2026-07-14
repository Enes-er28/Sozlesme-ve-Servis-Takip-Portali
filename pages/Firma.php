<?php 
ob_start();
include_once 'template/navbar2.php';
include_once 'template/sidebar.php';

$admin = new AdminClass();
$admin->sadece_admin();
$exceller = $admin->getExceller();

if (isset($_POST['save']) && $_POST['save'] == 1001) {
    $firma_ad = $admin->getSecurity($_POST['firma_ad']);
    $firma_turu = $admin->getSecurity($_POST['firma_turu']);
    $kimlik_no = $admin->getSecurity($_POST['kimlik_no']);
    $eta = $admin->getSecurity($_POST['eta'] ?? 'yok');
    $logo = $admin->getSecurity($_POST['logo'] ?? 'yok');
    $USR_code = $admin->getSecurity($_POST['USR_code'] ?? 'yok');
    $yetkili = $admin->getSecurity($_POST['yetkili'] ?? 'yok');
    $logo_kod = $admin->getSecurity($_POST['logo_kod'] ?? 'yok');
    $yetkili_eposta = $admin->getSecurity($_POST['yetkili_eposta'] ?? 'yok');
    $son_bakim_tarihi = !empty($_POST['son_bakim_tarihi']) ? $_POST['son_bakim_tarihi'] : null;

    $sql = "INSERT INTO firma (firma_ad, firma_turu, kimlik_no, eta, logo, [USR-code], yetkili, yetkili_eposta,logo_kod, son_bakim_tarihi) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $args = [$firma_ad, $firma_turu, $kimlik_no, $eta, $logo, $USR_code, $yetkili, $yetkili_eposta, $logo_kod, $son_bakim_tarihi];

    $insert_result = $admin->pdoInsert($sql, $args);

    if ($insert_result) {
        $firma_id = $admin->getLastInsertId();

        $sube_ad = "Merkez Şube";
        $adres = "Belirtilmedi";
        $telefon = "Belirtilmedi";

        $sql_sube = "INSERT INTO sube (sube_ad, firma_id, adres, telefon) VALUES (?, ?, ?, ?)";
        $args_sube = [$sube_ad, $firma_id, $adres, $telefon];

        $sube_insert = $admin->pdoInsert($sql_sube, $args_sube);
        header("Location: Firma.php"); exit();
        if (!$sube_insert) {
            echo "Merkez şube eklenemedi!";
        }
    } else {
        echo "Firma eklenemedi!";
    }
}

if (isset($_POST['update']) && $_POST['update'] == 1002) {
    $firma_id   = (int)$_POST['firma_id'];
    $firma_ad   = $admin->getSecurity($_POST['firma_ad']);
    $firma_turu = $admin->getSecurity($_POST['firma_turu']);
    $kimlik_no  = $admin->getSecurity($_POST['kimlik_no']);

    $eta = $admin->getSecurity($_POST['eta'] ?? 'yok');
    $logo = $admin->getSecurity($_POST['logo'] ?? 'yok');
    $USR_code = $admin->getSecurity($_POST['USR_code'] ?? 'yok');
    $yetkili = $admin->getSecurity($_POST['yetkili'] ?? 'yok');
    $logo_kod = $admin->getSecurity($_POST['logo_kod'] ?? 'yok');
    $yetkili_eposta = $admin->getSecurity($_POST['yetkili_eposta'] ?? 'yok');
    $son_bakim_tarihi = !empty($_POST['son_bakim_tarihi']) ? $_POST['son_bakim_tarihi'] : null;

    $sql = "UPDATE firma SET  
        firma_ad = ?, 
        firma_turu = ?, 
        kimlik_no = ?, 
        eta = ?, 
        logo = ?,
        [USR-code] = ?,
        yetkili = ?,
        yetkili_eposta = ?,
        logo_kod = ?,
        son_bakim_tarihi = ?
        WHERE firma_id = ?";

    $args = [
        $firma_ad, $firma_turu, $kimlik_no, $eta, $logo, $USR_code, $yetkili, $yetkili_eposta, $logo_kod, $son_bakim_tarihi, $firma_id
    ];

    if ($admin->pdoPrepare($sql, $args)) {
        header("Location: Firma.php");
        exit;
    } else {
        echo '<div class="alert alert-danger">İşlem Başarısız...</div>';
    }
}

if (isset($_POST['firma_id_delete'])) {
    $delete_id = intval($_POST['firma_id_delete']);
    $sql = "DELETE FROM firma WHERE firma_id = ?";
    $args = [$delete_id];
    $result = $admin->pdoDelete($sql,$args);
    header("Location: Firma.php"); exit();
}

if (isset($_POST['eta_eposta_indir'])) {
    $sql = "SELECT yetkili_eposta FROM firma WHERE eta = 'var'";
    $epostaList = $admin->pdoSelect($sql, []);
    $emails = array_map(function($row){
        return $row['yetkili_eposta'];
    }, $epostaList);
    $csv = implode(",", $emails);

    ob_clean();
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="eposta_listesi.txt"');
    echo $csv;
    exit(); 
}

if (isset($_POST['excel_bagla'])) {
    $firma_id = (int)$_POST['firma_id'];
    $excel_id = (int)$_POST['excel_id'];
    $admin->firmaExcelBagla($firma_id, $excel_id);
    header("Location: Firma.php?status=success"); 
    exit();
}
?>

<style>
.alfabe-btn:hover {
  background-color: #343a40 !important;
  color: #fff !important;
}
</style>

  <div class="content-wrapper">
   <section class="content-header"><h1>Cari Yönetimi</h1></section>
   <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card mb-4" style="box-shadow: 0 0 10px rgba(0,0,0,0.1); border-top: 3px solid #343a40;">
        <div class="card-body p-3">
          <table class="table table-borderless table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-center">
                  <h5 class="mb-3 text-secondary"><i class="fas fa-filter"></i> Alfabetik Filtreleme</h5>
                  <div class="d-flex justify-content-center flex-wrap" id="alfabe-container">
                    <?php 
                    $alfabe = ['A','B','C','Ç','D','E','F','G','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z','TÜMÜ'];
                    foreach ($alfabe as $harf): ?>
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
              <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-default-fe">Yeni Ekle</button>
              <button class="btn btn-success" data-toggle="modal" data-target="#modal-eta-eposta">
                  E-posta 
              </button>
        </div>
        <div class="card-body">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Cari Ünvanı</th>
                    <th>Şirket Türü</th>
                    <th>Yetkili </th>
                    <th>Vergi No / TC</th>        
                    <th>Yetkili Eposta</th>
                    <th>ETA</th>
                    <th>Logo</th>
                    <th>USR</th>
                    <th>ERP Kodu</th>
                    <th>Son Bakım Tarihi</th>
                    <th>İşlem</th>
                  </tr>
                </thead>
                <tbody id="ajax-firma-tablo-body">
<?php
$secili_harf = isset($_POST['harf']) ? mb_strtoupper($_POST['harf'], 'UTF-8') : 'A';
$variable = $admin->firma_Bilgi_Filtreli($secili_harf);

// Modalları biriktireceğimiz değişken
$modallar = ""; 

if ($variable) {
  foreach ($variable as $value) { 
?>
                  <tr>
                    <td><?php print htmlspecialchars($value['firma_ad']); ?></td>
                    <td><?php print htmlspecialchars($value['firma_turu']); ?></td>
                    <td><?php print htmlspecialchars($value['yetkili']); ?></td>
                    <td><?php print htmlspecialchars($value['kimlik_no']); ?></td>
                    <td><?php print htmlspecialchars($value['yetkili_eposta']); ?></td>
                    <td><?php print htmlspecialchars($value['eta']); ?></td>
                    <td><?php print htmlspecialchars($value['logo']); ?></td>
                    <td><?php print htmlspecialchars($value['USR-code']); ?></td>
                    <td><?php print htmlspecialchars($value['logo_kod']); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($value['son_bakim_tarihi'])); ?></td>
                    <td>
                      <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modal-default-fs<?php print $value['firma_id']; ?>">Sil</button>
                      <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modal-default-fg<?php print $value['firma_id']; ?>">Güncelle</button>
                      <?php if (!empty($value['excel_id'])): ?>
                          <button type="button" class="btn btn-info btn-sm" onclick="window.open('excel.php?excel_id=<?= $value['excel_id'] ?>','_blank')">
                              <i class="fas fa-table"></i> Tablo
                          </button>
                      <?php endif; ?>
                    </td>
                  </tr>

<?php 
// Her döngüde üretilen modalları $modallar değişkeninde toplayalım
ob_start(); 
?>
<div class="modal fade" id="modal-default-fs<?php print $value['firma_id']; ?>">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">Firma Sil</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Bu firmayı silmek istediğinize emin misiniz?</p>
          <p><strong>Firma ID:</strong> <?php print $value['firma_id']; ?></p>
          <p><strong>Firma Adı:</strong> <?php print htmlspecialchars($value['firma_ad']); ?></p>
          <input type="hidden" name="firma_id_delete" value="<?php print $value['firma_id']; ?>">
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          <button type="submit" class="btn btn-danger">Sil</button>
        </div>
        <input type="hidden" name="action" value="delete_firma">
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-default-fg<?php print $value['firma_id']; ?>">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h4 class="modal-title">Firma | Güncelle</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Firma Adı</label>
                <input type="text" class="form-control" name="firma_ad" value="<?php print htmlspecialchars($value['firma_ad']); ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Firma Türü</label>
                <select name="firma_turu" class="form-control" required onchange="toggleKimlikValidation(this, 'kimlik_no_edit<?php print $value['firma_id']; ?>')">
                  <option value="şahsi" <?php if($value['firma_turu'] == 'şahsi') echo 'selected'; ?>>Şahıs</option>
                  <option value="şirket" <?php if($value['firma_turu'] == 'şirket') echo 'selected'; ?>>Şirket</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Yetkili</label>
                <input type="text" class="form-control" name="yetkili" value="<?php print htmlspecialchars($value['yetkili']); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Kimlik No</label>
                <input type="text" class="form-control" id="kimlik_no_edit<?php print $value['firma_id']; ?>" name="kimlik_no" value="<?php print htmlspecialchars($value['kimlik_no']); ?>" maxlength="<?php echo ($value['firma_turu']=='şirket'?'10':'11'); ?>" pattern="<?php echo ($value['firma_turu']=='şirket'?'\\d{10}':'\\d{11}'); ?>" placeholder="<?php echo ($value['firma_turu']=='şirket'?'10 haneli vergi no':'11 haneli T.C. kimlik no'); ?>" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Yetkili Eposta</label>
                <input type="email" class="form-control" name="yetkili_eposta" value="<?php print htmlspecialchars($value['yetkili_eposta']); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>ETA</label>
                <select name="eta" class="form-control">
                  <option value="yok" <?php if($value['eta'] == 'yok') echo 'selected'; ?>>Yok</option>
                  <option value="var" <?php if($value['eta'] == 'var') echo 'selected'; ?>>Var</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Logo</label>
                <select name="logo" class="form-control">
                  <option value="yok" <?php if($value['logo'] == 'yok') echo 'selected'; ?>>Yok</option>
                  <option value="var" <?php if($value['logo'] == 'var') echo 'selected'; ?>>Var</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>USR</label>
                <input type="text" class="form-control" name="USR_code" value="<?php print htmlspecialchars($value['USR-code']); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>ERP Kodu</label>
                <input type="text" class="form-control" name="logo_kod" value="<?php print htmlspecialchars($value['logo_kod']); ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Son Bakım Tarihi</label>
                <input type="date" name="son_bakim_tarihi" value="<?= !empty($value['son_bakim_tarihi']) ? date('Y-m-d', strtotime($value['son_bakim_tarihi'])) : ''; ?>" class="form-control">
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          <button type="button" class="btn btn-success" onclick="excelModaliniAc(<?= $value['firma_id'] ?>, 'modal-default-fg<?= $value['firma_id'] ?>')">Tablo İlişkilendir</button>
          <?php if (!empty($value['excel_id'])): ?>
          <button type="button" class="btn btn-info" onclick="window.open('excel.php?excel_id=<?= $value['excel_id'] ?>','_blank')">Tablo Görüntüle</button>
          <?php endif; ?>
          <button type="submit" class="btn btn-primary">GÜNCELLE</button>
        </div>
        <input type="hidden" name="firma_id" value="<?php print $value['firma_id']; ?>">
        <input type="hidden" name="update" value="1002">
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-excel-sec-<?= $value['firma_id'] ?>" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h4 class="modal-title">Tablo  Bağla</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Firma: <strong><?= htmlspecialchars($value['firma_ad']) ?></strong></label>
                        <select name="excel_id" class="form-control" required>
                            <option value="">Seçiniz...</option>
                            <?php foreach($exceller as $ex): ?>
                                <option value="<?= $ex['excel_id'] ?>" <?= ($value['excel_id'] == $ex['excel_id'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($ex['excel_adi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" name="firma_id" value="<?= $value['firma_id'] ?>">
                    <input type="hidden" name="excel_bagla" value="1">
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php 
// Çıktıyı değişkene ekle
$modallar .= ob_get_clean();
  }
} 
?>
                </tbody>
                <tfoot>
                  <tr>
                    <th>Cari Ünvanı</th>
                    <th>Şirket Türü</th>
                    <th>Yetkili </th>
                    <th>Vergi No / TC</th>        
                    <th>Yetkili Eposta</th>
                    <th>ETA</th>
                    <th>Logo</th>
                    <th>USR</th>
                    <th>ERP Kodu</th>
                    <th>Son Bakım Tarihi</th>
                    <th>İşlem</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
    </div>
  </section>
</div>

<div id="ajax-modallar-alani">
    <?php echo isset($modallar) ? $modallar : ''; ?>
</div>

<div class="modal fade" id="modal-default-fe">
  <div class="modal-dialog modal-lg"> <div class="modal-content">
      <form method="POST" onsubmit="return checkKimlikNo(this)">
        <div class="modal-header">
          <h4 class="modal-title">Firma | Yeni Ekle</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Firma Adı</label>
                <input type="text" class="form-control" name="firma_ad" placeholder="Firma adı giriniz..." required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Firma Türü</label>
                <select name="firma_turu" class="form-control" required onchange="toggleKimlikValidation(this, 'kimlik_no_add')">
                  <option value="">Seçiniz</option>
                  <option value="şahsi">Şahıs</option>
                  <option value="şirket">Şirket</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Yetkili</label>
                <input type="text" class="form-control" name="yetkili" placeholder="Yetkili adı">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Kimlik No</label>
                <input type="text" class="form-control" id="kimlik_no_add" name="kimlik_no" maxlength="11" pattern="\d{11}" placeholder="11 haneli T.C. kimlik no" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Yetkili Eposta</label>
                <input type="email" class="form-control" name="yetkili_eposta" placeholder="Yetkili e-posta">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>ETA</label>
                <select name="eta" class="form-control">
                  <option value="yok" selected>Yok</option>
                  <option value="var">Var</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Logo</label>
                <select name="logo" class="form-control">
                  <option value="yok" selected>Yok</option>
                  <option value="var">Var</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>USR</label>
                <input type="text" class="form-control" name="USR_code" id="USR_code">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>ERP Kodu</label>
                <input type="text" class="form-control" name="logo_kod">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Son Bakım Tarihi</label>
                <input type="date" class="form-control" name="son_bakim_tarihi">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Vazgeç</button>
          <button type="submit" class="btn btn-primary">EKLE</button>
        </div>
        <input type="hidden" name="save" value="1001">
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modal-eta-eposta">
  <div class="modal-dialog modal-sm"> <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Özel İşlem</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <form method="GET" action="eta_eposta_indir.php" class="d-inline">
          <button type="submit" class="btn btn-primary m-2">ETA</button>
        </form>
        <form method="GET" action="logo_eposta_indir.php" class="d-inline">
          <button type="submit" class="btn btn-danger m-2">LOGO</button>
        </form>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Vazgeç</button>
      </div>
    </div>
  </div>
</div>

<script>
function toggleKimlikValidation(selectElement, kimlikInputId) {
  let tur = selectElement.value;
  let kimlikInput = document.getElementById(kimlikInputId);

  if (tur === 'şirket') {
    kimlikInput.setAttribute('maxlength', '10');
    kimlikInput.setAttribute('pattern', '\\d{10}');
    kimlikInput.placeholder = '10 haneli vergi no giriniz';
  } else {
    kimlikInput.setAttribute('maxlength', '11');
    kimlikInput.setAttribute('pattern', '\\d{11}');
    kimlikInput.placeholder = '11 haneli T.C. kimlik no giriniz';
  }
}

function validateTCKimlik(tc) {
  if (!/^[1-9][0-9]{10}$/.test(tc)) return false;

  let digits = tc.split('').map(Number);
  let oddSum  = digits[0] + digits[2] + digits[4] + digits[6] + digits[8];
  let evenSum = digits[1] + digits[3] + digits[5] + digits[7];

  let tenth = ((oddSum * 7) - evenSum) % 10;
  if (tenth !== digits[9]) return false;

  let firstTenSum = digits.slice(0,10).reduce((a,b)=>a+b,0);
  let eleventh = firstTenSum % 10;
  if (eleventh !== digits[10]) return false;

  return true;
}

function checkKimlikNo(form) {
  let tur = form.querySelector('[name="firma_turu"]').value;
  let tcInput = form.querySelector('[name="kimlik_no"]');
  let tc = tcInput.value.trim();

  if (tur === 'şahsi') {
    if (!validateTCKimlik(tc)) {
      alert("Geçerli bir T.C. Kimlik Numarası giriniz!");
      tcInput.focus();
      return false; 
    }
  }
  return true; 
}

$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('hata') === 'eksik_bilgi') {
        Swal.fire({
            icon: 'error',
            title: 'Eksik Bilgi!',
            text: 'Firma, Şube veya Müşteri seçimi yapmadan kayıt oluşturamazsınız.',
            confirmButtonText: 'Anladım',
            confirmButtonColor: '#d33',
            background: '#fff',
            backdrop: `rgba(255,0,0,0.1)`
        }).then(() => {
            window.history.replaceState({}, document.title, "eskidestek.php");
            $('#modal-add-e').modal('show');
        });
    }
});

function excelModaliniAc(firmaId, guncelleModalId) {
    $('#' + guncelleModalId).modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('body').css('padding-right', '');

    setTimeout(function() {
        $('#modal-excel-sec-' + firmaId).modal('show');
    }, 400); 
}

// ========================================================
// SİHİRLİ AJAX 2.0 (DATATABLES BYPASS SİSTEMİ)
// ========================================================
document.addEventListener("DOMContentLoaded", function() {
    const butonlar = document.querySelectorAll('.alfabe-btn');

    const varsayilanButon = document.querySelector('.alfabe-btn[data-secim="A"]');
    if(varsayilanButon) {
        varsayilanButon.classList.remove('bg-light', 'text-dark');
        varsayilanButon.classList.add('bg-dark', 'text-white');
    }

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
            let parser = new DOMParser();
            let doc = parser.parseFromString(html, 'text/html');
            
            let yeniSatirlar = doc.querySelectorAll('#ajax-firma-tablo-body tr');
            
            // MODAL GÜNCELLEMESİ (DOM'A EKLENEN YENİ KISIM)
            let yeniModallar = doc.querySelector('#ajax-modallar-alani');
            if (yeniModallar) {
                document.getElementById('ajax-modallar-alani').innerHTML = yeniModallar.innerHTML;
            }
            
            if ($.fn.DataTable.isDataTable('#example1')) {
                let dt = $('#example1').DataTable();
                
                dt.clear();
                
                yeniSatirlar.forEach(satir => {
                    if (!satir.querySelector('td[colspan]')) {
                        dt.row.add(satir);
                    }
                });
                
                dt.draw();
            } else {
                let temizTablo = doc.querySelector('#ajax-firma-tablo-body').innerHTML;
                document.getElementById('ajax-firma-tablo-body').innerHTML = temizTablo;
            }
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
});
</script>