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
$xStart = $hasFatura ? 20 : 30;
$yStart = 100;
$pdf->SetXY($xStart, $yStart);

// Ana tablo HTML
$html = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:10px;">';

// Başlık
$html .= '<tr style="font-weight:bold; background-color:#e6e6e6;">
    <th colspan="2" width="180" align="center">Açıklama</th>
    <th colspan="2" width="70" align="center">Baş.Bit.Trh</th>
    <th width="45" align="center">Miktar</th>
    <th width="70" align="center">Fiyat</th>';
if($hasFatura) $html .= '<th width="50" align="center">İndirim</th>';
$html .= '<th width="70" align="center">Tutar</th></tr>';

// Satırlar
$toplamFiyat = 0;
$toplamTutar = 0;
$kdvToplam = 0;
$toplam_indirimliKdv = 0;

foreach($detay['hareketler'] as $h) {
    $fiyat = floatval($h['fiyat'] ?? 0);
    $miktar = intval($h['miktar'] ?? 0); 
    $indirim = intval($h['indirim'] ?? 0); 
    $kdv = floatval($h['kdv'] ?? 0);

    // --- İndirim ve indirim_er uygulanıyor ---
    $fiyatIndirimli = $fiyat;
    if($indirim > 0){
        $fiyatIndirimli *= (1 - $indirim / 100);
    }


    // Tutar = indirimli fiyat × miktar
    $tutar = $fiyatIndirimli * $miktar;

    $indirimliKdv = $tutar * ($kdv / 100);

    $toplam_indirimliKdv += $indirimliKdv;

    $toplamFiyat += $fiyat * $miktar;
    $toplamTutar += $tutar;
    $kdvToplam += ($fiyat * $miktar) * ($kdv / 100);

    $basTarih = !empty($h['baslangic']) ? date("d.m.Y", strtotime($h['baslangic'])) : '';
    $bitTarih = !empty($h['bitis']) ? date("d.m.Y", strtotime($h['bitis'])) : '';
    $tarihText = trim($basTarih.' - '.$bitTarih, ' -');

    $html .= '<tr>
        <td width="40" align="center" valign="middle">'.htmlspecialchars($h['marka_ad'] ?? '').'</td>
        <td width="140">'.htmlspecialchars($h['model_ad'] ?? '').'</td>
        <td width="70" align="center" valign="middle">'.$tarihText.'</td>
        <td width="45" align="center" valign="middle">'.number_format($miktar, 0    , ',', '.').'</td>
        <td width="70" align="right">'.number_format($fiyat, 2, ',', '.').' </td>
        <td width="50" align="right">%'.number_format($indirim, 0, ',', '.').' </td>';

    $html .= '<td width="70" align="right">'.number_format($tutar, 2, ',', '.').' </td></tr>';
}

$html .= '</table>';


// Ana tabloyu PDF'e yaz
$pdf->writeHTML($html, true, false, true, false, '');

// --------------------- ALT TABLO ---------------------
// Ana tablonun bittiği Y koordinatını al
$altY = $pdf->GetY() - 6; // 2 birim boşluk bırak
$altX = $xStart + 104.1; // sağa yapışık
$pdf->SetXY($altX, $altY);

// Alt tablo HTML
$htmlAlt = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:9px;">';

$kdvToplam = 0; // KDV toplamı
foreach($detay['hareketler'] as $h) {
    $fiyat = floatval($h['fiyat'] ?? 0);
    $miktar = intval($h['miktar'] ?? 0);
    $kdv = floatval($h['kdv'] ?? 0);

    // KDV toplamı (indirimden bağımsız)
    $kdvToplam += ($fiyat * $miktar) * ($kdv / 100);
}

$htmlAlt .= '<tr style="background-color:#ffffff; font-weight:bold;">
    <td width="120" align="right">Brüt Toplam</td>
    <td width="70" align="right">'.number_format($toplamTutar, 2, ',', '.').' </td>
</tr>';

$htmlAlt .= '<tr style="background-color:#ffffff; font-weight:bold;">
    <td width="120" align="right">KDV</td>
    <td width="70" align="right">'.number_format($toplam_indirimliKdv , 2, ',', '.').' </td>
</tr>';

$htmlAlt .= '<tr style="background-color:#ffffff; font-weight:bold;">
    <td width="120" align="right">Genel Toplam (TL)</td>
    <td width="70" align="right">'.number_format($toplamTutar + $toplam_indirimliKdv , 2, ',', '.').' </td>
</tr>';

$htmlAlt .= '</table>';

// Alt tabloyu PDF'e yaz
$pdf->writeHTML($htmlAlt, true, false, true, false, '');









$pdf->Image('resimler/imza.jpg', 135, 170, 40); // X=15mm, Y=10mm, Genişlik=50mm


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
