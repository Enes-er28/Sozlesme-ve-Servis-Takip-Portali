<?php
// Çıkışı tamamen engelle ve önceki tamponu kapat
if (ob_get_level()) {
    ob_end_clean();
}
$adminclass = new AdminClass();

// E-posta verilerini çek
$sql = "SELECT yetkili_eposta FROM firma WHERE eta = 'var'";
$epostaList = $adminclass->pdoSelect($sql, []);

$emails = [];
if ($epostaList) {
    foreach ($epostaList as $row) {
        $emails[] = $row['yetkili_eposta'];
    }
}

// Dosya indirme header'ları
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="eposta_listesi.txt"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// E-posta listesini gönder
echo implode(",", $emails);
exit;