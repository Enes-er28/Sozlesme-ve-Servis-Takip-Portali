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

// Üst Görseller ve Çizgiler (Orijinal Koordinatlar)
$pdf->Image('resimler/erbilgisayar_logo.jpg', 30, 10, 100);
$pdf->Line(30, 30, 180, 30);

$pdf->SetXY(159, 31);
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 5, '' . date('d.m.Y'), 0, 1, 'L'); 

// Firma Bilgileri (Orijinal 30, 50-55-60 yapısı)
$firma_ad = $detay['firma_ad'] ?? 'Bilgi Yok';
$pdf->SetXY(30, 50);
$pdf->Cell(40, 6, 'Firma', 0, 0, 'L');
$pdf->Cell(40, 6, $firma_ad, 0, 1, 'L');
$pdf->SetXY(54, 50);
$pdf->Cell(40, 6, ':', 0, 0, 'L');

$yetkili = $detay['yetkili'] ?? 'Bilgi Yok';
$pdf->SetXY(30, 55);
$pdf->Cell(40, 6, 'İlgili', 0, 0, 'L');
$pdf->Cell(40, 6, $yetkili, 0, 1, 'L');
$pdf->SetXY(54, 55);
$pdf->Cell(40, 6, ':', 0, 0, 'L');

$konu = 'Abonelik & Destek Yenilemesi';
$pdf->SetXY(30, 60);
$pdf->Cell(40, 6, 'Konu', 0, 0, 'L');
$pdf->SetXY(54, 60);
$pdf->Cell(40, 6, ':', 0, 0, 'L');
$pdf->SetXY(70, 60);
$pdf->Cell(0, 6, $konu, 0, 0, 'L');

// Metinler
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

// Ana tablo X-Y koordinatı (Orijinal 30, 100 yapısı)
$xStart = 20;
$yStart = 100;
$pdf->SetXY($xStart, $yStart);

// Ana tablo HTML (Genişlikler orijinal ölçülere çekildi)
$html = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:10px;">';
$html .= '<tr style="font-weight:bold; background-color:#e6e6e6;">
    <th colspan="2" width="180" align="center">Alınan Hizmet</th>
    <th colspan="2" width="100" align="center">İlgili Alan Adı</th>
    <th colspan="2" width="70" align="center">Bitiş Tarihi</th>
    <th width="65" align="center">Döngü</th>
    <th width="70" align="center">Tutar</th>
</tr>';

/* --- TOPLAM DEĞİŞKENLERİ --- */
$kdvMatrah    = 0;
$kdvToplam    = 0;
$ozelToplam   = 0; // Faturasız toplam
$genelToplam  = 0;

foreach($detay['hareketler'] as $h) {
    $fiyat   = floatval($h['fiyat'] ?? 0);
    $miktar  = intval($h['miktar'] ?? 1); 
    $indirim = floatval($h['indirim'] ?? 0); 
    $kdvOran = floatval($h['kdv'] ?? 0);
    $fatura  = !empty($h['fatura']);

    $fiyatIndirimli = $fiyat * (1 - ($indirim / 100));
    $tutar = $fiyatIndirimli * $miktar;

    // Fatura yoksa kırmızı yazdır
    $renkStyle = !$fatura ? ' style="color:#c50000;"' : '';

    if ($fatura) {
        $kdvMatrah  += $tutar;
        $kdvToplam  += $tutar * ($kdvOran / 100);
        $genelToplam += ($tutar + ($tutar * ($kdvOran / 100)));
    } else {
        $ozelToplam += $tutar;
    }

    $bitTarih = !empty($h['baslangic']) ? date("d.m.Y", strtotime($h['baslangic'].' -1 day')) : '';

    $html .= '<tr'.$renkStyle.'>
        <td width="50" align="center">'.htmlspecialchars($h['marka_ad'] ?? '').'</td>
        <td width="130">'.htmlspecialchars($h['model_ad'] ?? '').'</td>
        <td width="100" align="center">'.htmlspecialchars($h['aciklama'] ?? '').'</td>
        <td width="70" align="center">'.$bitTarih.'</td>
        <td width="65" align="center">'.htmlspecialchars($h['dongu'] ?? '').'</td>
        <td width="70" align="right">'.number_format($fiyat, 2, ',', '.').'</td>
    </tr>';
}

$html .= '</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// --- ALT TABLO (Orijinal Konum: Sağ Alt) ---
$altY = $pdf->GetY() - 5.65;
$altX = $xStart + 98.9; 
$pdf->SetXY($altX, $altY);

$htmlAlt = '<table border="1" cellpadding="2" cellspacing="0" style="font-size:9px;">';
$htmlAlt .= '<tr>
    <td width="105">KDV Matrahı</td>
    <td width="100" align="right">'.number_format($kdvMatrah, 2, ',', '.').'</td>
</tr>';
$htmlAlt .= '<tr>
    <td>KDV Toplamı</td>
    <td width="100" align="right">'.number_format($kdvToplam, 2, ',', '.').'</td>
</tr>';
$htmlAlt .= '<tr style="font-weight:bold;">
    <td>Genel Toplam</td>
    <td width="100" align="right">'.number_format($genelToplam, 2, ',', '.').'</td>
</tr>';

if ($ozelToplam > 0) {
    $htmlAlt .= '<tr style="color:#c50000; font-weight:bold;">
        <td>Özel Toplam</td>
        <td width="100" align="right">'.number_format($ozelToplam, 2, ',', '.').'</td>
    </tr>';
}
$htmlAlt .= '</table>';
$pdf->writeHTML($htmlAlt, true, false, true, false, '');

// İmza ve Alt Görsel (Orijinal Koordinatlar)
$pdf->Image('resimler/imza.jpg', 135, 185, 30);

$pdf->SetXY(30, 210);
$pdf->Cell(0, 6, 'Abonelik Ürününü & Hizmet Sözleşmesinin', 0, 0, 'L');
$pdf->SetXY(30, 215);
$pdf->Cell(0, 6, 'Yenilenmesini Onaylıyorum.', 0, 0, 'L');

$pdf->SetXY(30, 225);
$pdf->Cell(0, 6, 'Adı, Soyadı, Kaşe, İmza      :', 0, 0, 'L');
$pdf->Line(30, 230, 80, 230);

$pdf->Image('resimler/Genel_Antet_Alt_Firuzkoy.jpg', 30, 269, 155);

$pdf->Output('teklif_'.$teklif_id.'.pdf', 'I');