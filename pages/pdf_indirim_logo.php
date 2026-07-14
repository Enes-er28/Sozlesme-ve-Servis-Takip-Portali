<?php
require_once __DIR__ . '/../data/class.php';
require_once('tcpdf/tcpdf.php');
if (ob_get_length()) {
    ob_end_clean();
}

$admin = new AdminClass();

// URL'den teklif_id al
$teklif_id = isset($_GET['q']) ? (int)$_GET['q'] : 0;
if ($teklif_id <= 0) die("Geçersiz teklif ID");

// Teklif detayını al
$detay = $admin->getTeklifDetay($teklif_id);
if(!$detay) die("Teklif bulunamadı");

// TCPDF başlat
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);

$pdf->Image('resimler/erbilgisayar_logo.jpg', 30, 10, 100);
$pdf->Line(30, 30, 180, 30);

$pdf->SetXY(159, 31);
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 5, date('d.m.Y'), 0, 1, 'L');

// Firma
$firma_ad = $detay['firma_ad'] ?? 'Bilgi Yok';
$pdf->SetXY(30, 50);
$pdf->Cell(40, 6, 'Firma', 0, 0, 'L');
$pdf->Cell(40, 6, $firma_ad, 0, 1, 'L');
$pdf->SetXY(54, 50);
$pdf->Cell(40, 6, ':', 0, 0, 'L');

// Yetkili
$yetkili = $detay['yetkili'] ?? 'Bilgi Yok';
$pdf->SetXY(30, 55);
$pdf->Cell(40, 6, 'İlgili', 0, 0, 'L');
$pdf->Cell(40, 6, $yetkili, 0, 1, 'L');
$pdf->SetXY(54, 55);
$pdf->Cell(40, 6, ':', 0, 0, 'L');

// Konu
$konu = 'Abonelik & Destek Yenilemesi';
$pdf->SetXY(30, 60);
$pdf->Cell(40, 6, 'Konu', 0, 0, 'L');
$pdf->SetXY(54, 60);
$pdf->Cell(40, 6, ':', 0, 0, 'L');
$pdf->SetXY(70, 60);
$pdf->Cell(0, 6, $konu, 0, 0, 'L');

$pdf->SetXY(40, 70);
$pdf->Cell(0, 6, 'Aşağıda belirtilen abonelik ürününü & hizmet sözleşmesinin bitiş tarihi', 0, 0, 'L');
$pdf->SetXY(30, 75);
$pdf->Cell(0, 6, 'yaklaşmıştır.', 0, 0, 'L');
$pdf->SetXY(40, 80);
$pdf->Cell(0, 6, 'Yenileme teklifimiz aşagıdadır.', 0, 0, 'L');

$pdf->SetFont('dejavusans', '', 14);
$pdf->SetXY(15, 90);
$pdf->Cell(0, 10, 'TEKLİF MEKTUBU', 0, 1, 'C');

$pdf->SetFont('dejavusans', '', 10);

/* ---------------- TOPLAM DEĞİŞKENLER ---------------- */

$kdvMatrah   = 0;
$kdvToplam   = 0;
$kdvsizToplam = 0;
$genelToplam = 0;

/* ---------------- ANA TABLO ---------------- */

$xStart = 7;
$yStart = 100;
$pdf->SetXY($xStart, $yStart);

$html = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:10px;">';

$html .= '<tr style="font-weight:bold; background-color:#e6e6e6;">
    <th colspan="2" width="180" align="center">Açıklama</th>
    <th width="70" align="center">Baş.Bit.Trh</th>
    <th width="45" align="center">Miktar</th>
    <th width="70" align="center">Fiyat</th>
    <th width="55" align="center">Sdkt İnd</th>
    <th width="65" align="center">ErBilg.İnd.</th>
    <th width="70" align="center">Tutar</th>
</tr>';

foreach($detay['hareketler'] as $h){

    $fiyat   = (float)($h['fiyat'] ?? 0);
    $miktar  = (int)($h['miktar'] ?? 0);
    $indirim = (float)($h['indirim'] ?? 0);
    $indirim_er = (float)($h['indirim_er'] ?? 0);
    $kdv     = (float)($h['kdv'] ?? 0);
    $fatura  = !empty($h['fatura']);

    $renkStyle = $fatura ? '' : ' style="color:#c50000;"';

    $fiyatIndirimli = $fiyat;
    if($indirim > 0) $fiyatIndirimli *= (1 - $indirim/100);
    if($indirim_er > 0) $fiyatIndirimli *= (1 - $indirim_er/100);

    $tutar = $fiyatIndirimli * $miktar;

    if ($fatura) {
        $kdvTutar = $tutar * ($kdv/100);
        $kdvMatrah += $tutar;
        $kdvToplam += $kdvTutar;
        $genelToplam += ($tutar + $kdvTutar);
    } else {
        $kdvsizToplam += $tutar;
    }

    $basTarih = !empty($h['baslangic']) ? date("d.m.Y", strtotime($h['baslangic'])) : '';
    $bitTarih = !empty($h['bitis']) ? date("d.m.Y", strtotime($h['bitis'])) : '';
    $tarihText = trim($basTarih.' - '.$bitTarih, ' -');

    $html .= '<tr'.$renkStyle.'>
        <td width="40" align="center">'.htmlspecialchars($h['marka_ad'] ?? '').'</td>
        <td width="140">'.htmlspecialchars($h['model_ad'] ?? '').'</td>
        <td width="70" align="center">'.$tarihText.'</td>
        <td width="45" align="center">'.number_format($miktar,0,',','.').'</td>
        <td width="70" align="right">'.number_format($fiyat,2,',','.').'</td>
        <td width="55" align="center">%'.number_format($indirim,0,',','.').'</td>
        <td width="65" align="center">%'.number_format($indirim_er,0,',','.').'</td>
        <td width="70" align="right">'.number_format($tutar,2,',','.').'</td>
    </tr>';
}

$html .= '</table>';

$pdf->writeHTML($html, true, false, true, false, '');



$altY = $pdf->GetY() - 5.5;
$altX = $xStart + 104.1;
$pdf->SetXY($altX, $altY);

$htmlAlt = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:9px;">';

// KDV Matrahı
$htmlAlt .= '<tr>
    <td>KDV Matrahı</td>
    <td width="134" align="right">'.number_format($kdvMatrah,2,',','.').'</td>
</tr>';

// KDV Toplamı
$htmlAlt .= '<tr>
    <td>KDV Toplamı</td>
    <td width="134" align="right">'.number_format($kdvToplam,2,',','.').'</td>
</tr>';

// Genel Toplam (sadece KDV'li genel)
$htmlAlt .= '<tr style="font-weight:bold;">
    <td>Genel Toplam</td>
    <td width="134" align="right">'.number_format($kdvMatrah + $kdvToplam,2,',','.').'</td>
</tr>';

// Özel Toplam (KDV'sizler)
if ($kdvsizToplam > 0) {
    $htmlAlt .= '<tr>
        <td color="#c50000">Özel Toplam</td>
        <td width="134" align="right" color="#c50000">'.number_format($kdvsizToplam,2,',','.').'</td>
    </tr>';
}

$htmlAlt .= '</table>';

$pdf->writeHTML($htmlAlt, true, false, true, false, '');


$pdf->Image('resimler/imza.jpg', 135, 170, 40);

$pdf->SetXY(30, 210);
$pdf->Cell(0, 6, 'Abonelik Ürününü & Hizmet Sözleşmesinin', 0, 0, 'L');
$pdf->SetXY(30, 215);
$pdf->Cell(0, 6, 'Yenilenmesini Onaylıyorum.', 0, 0, 'L');

$pdf->SetXY(30, 225);
$pdf->Cell(0, 6, 'Adı, Soyadı, Kaşe, İmza      :', 0, 0, 'L');
$pdf->Line(30, 230, 80, 230);

$pdf->Image('resimler/Logo_Alt.jpg', 30, 259, 155);

$pdf->Output('teklif_'.$teklif_id.'.pdf', 'I');
