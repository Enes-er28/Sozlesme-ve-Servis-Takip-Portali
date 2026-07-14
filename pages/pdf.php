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




$pdf->Image('resimler/erbilgisayar_logo.jpg', 30, 10, 100); // X=15mm, Y=10mm, Genişlik=50mm

$pdf->Line(30, 30, 180, 30); // X=10,Y=50 den X=200,Y=50 e düz çizgi

$pdf->SetXY(159, 31); // X ve Y koordinatı (Y çizgi altına 1mm aşağı)
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 5, '' . date('d.m.Y'), 0, 1, 'L'); 



// Firma adı yaz
$firma_ad = $detay['firma_ad'] ?? 'Bilgi Yok';
$pdf->SetXY(30, 50);
$pdf->Cell(40, 6, 'Firma', 0, 0, 'L'); // 40mm genişlikte sol hizalı
$pdf->Cell(40, 6, $firma_ad, 0, 1, 'L'); // Firma adı
$pdf->SetXY(54, 50);
$pdf->Cell(40, 6, ':', 0, 0, 'L'); // 40mm genişlikte sol hizalı

// Firma yetkili
$yetkili = $detay['yetkili'] ?? 'Bilgi Yok';
$pdf->SetXY(30, 55);
$pdf->Cell(40, 6, 'İlgili', 0, 0, 'L'); // Etiket
$pdf->Cell(40, 6, $yetkili, 0, 1, 'L'); // Yetkili adı
$pdf->SetXY(54, 55);
$pdf->Cell(40, 6, ':', 0, 0, 'L'); // 40mm genişlikte sol hizalı

// Konu
$konu = 'Abonelik & Destek Yenilemesi'; // Sabit metin, istersen DB’den de çekebilirsin
$pdf->SetXY(30, 60);
$pdf->Cell(40, 6, 'Konu', 0, 0, 'L');
$pdf->SetXY(54, 60);
$pdf->Cell(40, 6, ':', 0, 0, 'L');
$pdf->SetXY(70, 60);
$pdf->Cell(0, 6, $konu, 0, 0, 'L');


$pdf->SetXY(40, 70); // X ve Y konumu
$pdf->Cell(0, 6, 'Aşağıda belirtilen abonelik ürününü & hizmet sözleşmesinin bitiş tarihi', 0, 0, 'L');

$pdf->SetXY(30, 75); // X ve Y konumu
$pdf->Cell(0, 6, 'yaklaşmıştır.', 0, 0, 'L');

$pdf->SetXY(40, 80); // X ve Y konumu
$pdf->Cell(0, 6, 'Yenileme teklifimiz aşagıdadır.', 0, 0, 'L');






$pdf->SetFont('dejavusans', '', 14); // İnce, boyut 14
$pdf->SetXY(15, 90); // TEKLİF MEKTUBU konumu
$pdf->Cell(0, 10, 'TEKLİF MEKTUBU', 0, 1, 'C'); // 'C' ortalı

$pdf->SetFont('dejavusans', '', 10);
// KDV var mı kontrol
$hasFatura = true;
foreach($detay['hareketler'] as $h) {
    if(!empty($h['fatura'])) { $hasFatura = true; break; }
}

// Ana tablo X-Y koordinatı
$xStart = $hasFatura ? 15 : 30;
$yStart = 100;
$pdf->SetXY($xStart, $yStart);

// Ana tablo HTML
$html = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:10px;">';
$html .= '<tr style="font-weight:bold; background-color:#e6e6e6;">
    <th colspan="2" width="180" align="center">Açıklama</th>
    <th colspan="2" width="70" align="center">Baş.Bit.Trh</th>
    <th width="45" align="center">Miktar</th>
    <th width="70" align="center">Fiyat</th>
    <th width="70" align="center">KDV</th>
    <th width="70" align="center">Tutar</th>
</tr>';

/* ---------- TOPLAM DEĞİŞKENLER ---------- */

$kdvMatrah   = 0;
$kdvToplam   = 0;
$kdvsizToplam = 0;
$genelToplam = 0;

/* ---------- SATIRLAR ---------- */

foreach($detay['hareketler'] as $h){

    $fiyat   = (float)($h['fiyat'] ?? 0);
    $miktar  = (int)($h['miktar'] ?? 0);
    $kdv     = (float)($h['kdv'] ?? 0);
    $fatura  = !empty($h['fatura']);

    $renkStyle = $fatura ? '' : ' style="color:#c50000;"';

    // Satır ara toplam (KDV hariç)
    $tutar = $fiyat * $miktar;

    if ($fatura) {
        // KDV'li hesap
        $kdvTutar = $tutar * ($kdv / 100);

        $kdvMatrah += $tutar;
        $kdvToplam += $kdvTutar;
        $genelToplam += ($tutar + $kdvTutar);

        $tutarYaz = $tutar ;
    } else {
        // KDV'siz özel satır
        $kdvsizToplam += $tutar;

        // ❗ Genel toplama EKLENMEYECEK
        $tutarYaz = $tutar;
    }


    $basTarih = !empty($h['baslangic']) ? date("d.m.Y", strtotime($h['baslangic'])) : '';
    $bitTarih = !empty($h['bitis']) ? date("d.m.Y", strtotime($h['bitis'])) : '';
    $tarihText = trim($basTarih.' - '.$bitTarih, ' -');
    // KDV sütunu gösterimi
    $kdvGoster = $fatura ? '%'.number_format($kdv,0) : '';


    $html .= '<tr'.$renkStyle.'>
        <td width="40" align="center">'.htmlspecialchars($h['marka_ad'] ?? '').'</td>
        <td width="140">'.htmlspecialchars($h['model_ad'] ?? '').'</td>
        <td width="70" align="center">'.$tarihText.'</td>
        <td width="45" align="center">'.number_format($miktar,0,',','.').'</td>
        <td width="70" align="right">'.number_format($fiyat,2,',','.').'</td>
        <td width="70" align="center">'.$kdvGoster.'</td>
        <td width="70" align="right">'.number_format($tutarYaz,2,',','.').'</td>
    </tr>';

}


$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

/* ---------- ALT TABLO ---------- */

$altY = $pdf->GetY() - 6;
$altX = $xStart + 104.1;
$pdf->SetXY($altX, $altY);

$htmlAlt = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:9px;">
<tr>
    <td >KDV Matrahı </td>
    <td width="95" align="right">'.number_format($kdvMatrah,2,',','.').'</td>
</tr>
<tr>
    <td>KDV Toplamı</td>
    <td width="95" align="right">'.number_format($kdvToplam,2,',','.').'</td>
</tr>
<tr>
    <td>KDV\'siz Toplam</td>
    <td width="95" align="right">'.number_format($kdvsizToplam,2,',','.').'</td>
</tr>
<tr style="font-weight:bold; color:red;">
    <td>Genel Toplam</td>
    <td width="95" align="right">'.number_format($genelToplam,2,',','.').'</td>
</tr>
</table>';



// --------------------- ALT TABLO ---------------------
// Ana tablonun bittiği Y koordinatını al
$altY = $pdf->GetY() - 0; // 2 birim boşluk bırak
$altX = $xStart + 104.1; // sağa yapışık
$pdf->SetXY($altX, $altY);

// Alt tablo HTML
$htmlAlt = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:9px;">';



$htmlAlt .= '<tr>
    <td>KDV Matrahı</td>
    <td width="95"align="right">'.number_format($kdvMatrah,2,',','.').'</td>
</tr>';

$htmlAlt .= '<tr>
    <td>KDV Toplamı</td>
    <td width="95"align="right">'.number_format($kdvToplam,2,',','.').'</td>
</tr>';

$htmlAlt .= '<tr style="font-weight:bold;">
    <td>Genel Toplam</td>
    <td width="95"align="right">'.number_format($genelToplam,2,',','.').'</td>
</tr>';

if ($kdvsizToplam > 0) {
    $htmlAlt .= '<tr>
        <td color="#c50000">Özel Toplam</td>
        <td width="95" align="right" color="#c50000">'.number_format($kdvsizToplam,2,',','.').'</td>
    </tr>';
}

$htmlAlt .= '</table>';

// Alt tabloyu PDF'e yaz
$pdf->writeHTML($htmlAlt, true, false, true, false, '');










$pdf->Image('resimler/imza.jpg', 135, 185, 30); // X=15mm, Y=10mm, Genişlik=50mm


$pdf->SetXY(30, 210); // X ve Y konumu
$pdf->Cell(0, 6, 'Abonelik Ürününü & Hizmet Sözleşmesinin', 0, 0, 'L');
$pdf->SetXY(30, 215); // X ve Y konumu
$pdf->Cell(0, 6, 'Yenilenmesini Onaylıyorum.', 0, 0, 'L');


$pdf->SetXY(30, 225);
$pdf->Cell(0, 6, 'Adı, Soyadı, Kaşe, İmza      :', 0, 0, 'L');
$pdf->Line(30, 230, 80, 230); // X=10,Y=50 den X=200,Y=50 e düz çizgi





$pdf->Image('resimler/Genel_Antet_Alt_Firuzkoy.jpg', 30, 269, 155); // X=15mm, Y=10mm, Genişlik=50mm




// Çıktı
$pdf->Output('teklif_'.$teklif_id.'.pdf', 'I');
