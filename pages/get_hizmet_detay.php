<?php
include_once 'template/navbar.php';

$admin = new AdminClass();
$abone_hizmet_id = isset($_GET['abone_hizmet_id']) ? (int)$_GET['abone_hizmet_id'] : 0;

$detaylar = $admin->getAboneHizmetHareketleri($abone_hizmet_id);

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


if ($detaylar):
?>
<table class="table table-sm table-bordered">
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
      <th>Tutar</th>
      <th>Fatura</th>
      <th>Detay</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($detaylar as $detay): ?>
      <tr>
        <td><?= htmlspecialchars($detay['marka_ad']) ?></td>
        <td><?= htmlspecialchars($detay['model_ad']) ?></td>
        <td><?= htmlspecialchars($detay['aciklama']) ?></td>
        <td><?= formatDate($detay['baslangic']) ?></td>
        <td><?= formatDate($detay['bitis']) ?></td>
        <td><?= htmlspecialchars($detay['dongu']) ?></td>
        <td><?= $detay['miktar'] ?></td>
        <td><?= $detay['fiyat'] ?></td>
        <td><?= $detay['tutar'] ?></td>
        <td><?= $detay['fatura'] ? 'Var' : 'Yok' ?></td>
        <td><?= htmlspecialchars($detay['detay']) ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<div class="text-center">Detay bulunamadı.</div>
<?php endif; ?>
