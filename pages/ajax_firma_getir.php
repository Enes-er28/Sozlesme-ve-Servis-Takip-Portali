<?php
// 1. SİHİRLİ DEĞNEK: index.php'nin yukarıdan zorla eklediği menüleri çöpe atar (Temiz sayfa açar)
ob_clean();

require_once __DIR__ . '/../data/class.php';
$admin = new AdminClass();
$admin->sadece_admin();

// Modal içerisinde excel listesi kullanıldığı için onu da çekiyoruz
$exceller = $admin->getExceller();

// JS'den gelen harfi al
$harf = isset($_POST['harf']) ? mb_strtoupper($_POST['harf'], 'UTF-8') : 'A';

// Filtrelenmiş firmaları veritabanından çek
$firmalar = $admin->firma_Bilgi_Filtreli($harf);

if (empty($firmalar)) {
    echo '<tr><td colspan="11" class="text-center font-weight-bold py-4">Bu harf ile başlayan firma bulunamadı.</td></tr>';
    exit();
}

// Firmaları döngüye sokup doğrudan HTML satırlarını geri döndürüyoruz
foreach ($firmalar as $value) {
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
                    <h4 class="modal-title">Tablo Bağla</h4>
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
}

// 2. SİHİRLİ DEĞNEK: index.php'nin en alta ekleyeceği gereksiz her şeyi durdurur
exit;
?>