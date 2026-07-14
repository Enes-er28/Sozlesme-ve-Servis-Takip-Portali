<?php
require_once __DIR__ . '/../data/class.php';


if (ob_get_length()) {
    ob_end_clean();
}


$admin = new AdminClass();
$pdo = $admin->getPdo();

$teklif_id = isset($_GET['teklif_id']) ? (int)$_GET['teklif_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM bekleyen_teklifler WHERE teklif_id = ?");
$stmt->execute([$teklif_id]);
$teklif = $stmt->fetch(PDO::FETCH_ASSOC);

require_once('tcpdf/tcpdf.php');

$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);

$pdf->Image('resimler/erbilgisayar_logo.jpg', 30, 10, 100); // X=15mm, Y=10mm, Genişlik=50mm

$pdf->Line(30, 30, 180, 30); // X=10,Y=50 den X=200,Y=50 e düz çizgi

$pdf->SetXY(126, 31); // X ve Y koordinatı (Y çizgi altına 1mm aşağı)
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 5, 'Oluşturma Tarihi: ' . date('d.m.Y'), 0, 1, 'L'); 

// Firma adı yaz
// Firma adı yaz (firma_id'den firma_ad alınıyor)
$firma_ad = 'Bilgi Yok';
if (isset($teklif['firma_id'])) {
    $firma_id = $teklif['firma_id'];
    $stmtFirma = $pdo->prepare("SELECT firma_ad FROM firma WHERE firma_id = ?");
    $stmtFirma->execute([$firma_id]);
    $firma = $stmtFirma->fetch(PDO::FETCH_ASSOC);
    if ($firma) {
        $firma_ad = $firma['firma_ad'];
    }
}
$pdf->SetXY(30, 50);
$pdf->Cell(40, 6, 'Firma', 0, 0, 'L'); // 40mm genişlikte sol hizalı
$pdf->Cell(40, 6, $firma_ad, 0, 1, 'L'); // geri kalan alanı firma adı için kullan
$pdf->SetXY(54, 50);
$pdf->Cell(40, 6, ':', 0, 0, 'L'); // 40mm genişlikte sol hizalı



// Firma yetkili
$yetkili = 'Bilgi Yok';
if (isset($teklif['firma_id'])) {
    $firma_id = $teklif['firma_id'];
    $stmtFirma = $pdo->prepare("SELECT yetkili FROM firma WHERE firma_id = ?");
    $stmtFirma->execute([$firma_id]);
    $firma = $stmtFirma->fetch(PDO::FETCH_ASSOC);
    if ($firma) {
        $yetkili = $firma['yetkili'];
    }
}
$pdf->SetXY(30, 55);
$pdf->Cell(40, 6, 'İlgili', 0, 0, 'L'); // Etiket için 40mm genişlik
$pdf->Cell(40, 6, $yetkili, 0, 1, 'L'); // Yetkili adı
$pdf->SetXY(54, 55);
$pdf->Cell(40, 6, ':', 0, 0, 'L'); // 40mm genişlikte sol hizalı

$pdf->SetXY(30, 60);
$pdf->Cell(40, 6, 'Konu', 0, 0, 'L'); // Etiket için 40mm genişlik
$pdf->SetXY(54, 60);
$pdf->Cell(40, 6, ':', 0, 0, 'L'); // 40mm genişlikte sol hizalı
$pdf->SetXY(70, 60);
$pdf->Cell(0, 6, 'Destek Anlaşmasının Yenilemesi', 0, 0, 'L'); // Etiket için 40mm genişlik

// Metin cümlesi
$pdf->SetXY(40, 70); // X ve Y konumu
$pdf->Cell(0, 6, 'Firmamızla yapmış olduğunuz', 0, 0, 'L');
// hiz_dongu
if (!empty($teklif['hiz_dongu'])) {
    $hiz_dongu = $teklif['hiz_dongu']; // direkt string al
} else {
    $hiz_dongu = 'Bilgi Yok';
}
$pdf->SetXY(94, 70); // X ve Y konumu
$pdf->Cell(0, 6, $hiz_dongu, 0, 0, 'L');
$pdf->SetXY(103, 70); // X ve Y konumu
$pdf->Cell(0, 6, 'Destek Anlaşması', 0, 0, 'L');

// Satır boşluğu bırakmak için (opsiyonel)
$pdf->Ln(2); // 2mm aşağı kaydır

// Hizmet Bitiş Tarihi
if (isset($teklif['hizmet_bitis_tarih']) && strtotime($teklif['hizmet_bitis_tarih']) !== false) {
    $bitis_tarih = date('d/m/Y', strtotime($teklif['hizmet_bitis_tarih']));
} else {
    $bitis_tarih = 'Bilgi Yok';
}
$pdf->SetXY(136, 70); // X ve Y konumu
$pdf->Cell(0, 6, '' . $bitis_tarih, 0, 0, 'L');

$pdf->SetXY(158, 70); // X ve Y konumu
$pdf->Cell(0, 6, 'tarihinde sona ', 0, 0, 'L');
$pdf->SetXY(30, 75); // X ve Y konumu
$pdf->Cell(0, 6, 'erecektir.', 0, 0, 'L');

$pdf->SetXY(40, 90); // X ve Y konumu
$pdf->Cell(0, 6, 'Yeni Dönem için Teklifimiz;', 0, 0, 'L');

$pdf->SetXY(30, 95); // X ve Y konumu
$pdf->Cell(0, 6, 'Danışmanlık ve Uzak destek olarak geçen yılın fiyatı ile', 0, 0, 'L');
// Hizmet fiyat
if (isset($teklif['hizmet_fiyat']) && is_numeric($teklif['hizmet_fiyat'])) {
    $hizmet_fiyat = number_format($teklif['hizmet_fiyat'], 2, ',', '.') . ' ₺'; // 2 ondalık ve TL işareti
} else {
    $hizmet_fiyat = 'Bilgi Yok';
}
$pdf->SetXY(129, 95); // X ve Y konumu
$pdf->Cell(0, 6, '' . $hizmet_fiyat, 0, 0, 'L');

$pdf->SetXY(144, 95); // X ve Y konumu
$pdf->Cell(0, 6, "+ KDV'dir. ", 0, 0, 'L');
$pdf->SetXY(30, 100); // X ve Y konumu
$pdf->Cell(0, 6, 'Yerinde servis olacaksa fiyat iki katıdır.', 0, 0, 'L');
$pdf->SetXY(30, 105); // X ve Y konumu
$pdf->Cell(0, 6, 'Yıl içerisinde geçiş yapılırsa gün olarak hesaplanarak yerinde desteğe geçiş yapılabilir.', 0, 0, 'L');

$pdf->SetXY(40, 120); // X ve Y konumu
$pdf->Cell(0, 6, 'Teklifimizin kabulü halinde aşağıdaki bölümün doldurulup tarafımıza gönderilmesini', 0, 0, 'L');
$pdf->SetXY(30, 125); // X ve Y konumu
$pdf->Cell(0, 6, 'rica ederiz.', 0, 0, 'L');



$pdf->Image('resimler/imza.jpg', 135, 150, 40); // X=15mm, Y=10mm, Genişlik=50mm


$pdf->SetXY(30, 200); // X ve Y konumu
$pdf->Cell(0, 6, 'Yıllık Danışmanlık ve Uzak Destek Sözleşmesinin', 0, 0, 'L');
$pdf->SetXY(30, 205); // X ve Y konumu
$pdf->Cell(0, 6, 'Yenilenmesini Onaylıyorum.', 0, 0, 'L');


if (isset($teklif['hizmet_baslangic_tarih']) && strtotime($teklif['hizmet_baslangic_tarih']) !== false) {
    $hizmet_baslangic_tarih = date('d/m/Y', strtotime($teklif['hizmet_baslangic_tarih']));
} else {
    $hizmet_baslangic_tarih = 'Bilgi Yok';
}
$pdf->SetXY(30, 215); // X ve Y konumu
$pdf->Cell(0, 6, '' . $hizmet_baslangic_tarih , 0, 0, 'L');
$pdf->SetXY(52, 215); // X ve Y konumu
$pdf->Cell(0, 6, '—', 0, 0, 'L'); // em dash (daha uzun)

if (isset($teklif['hizmet_bitis_tarih']) && strtotime($teklif['hizmet_bitis_tarih']) !== false) {
    $bitis_tarih = date('d/m/Y', strtotime($teklif['hizmet_bitis_tarih']));
} else {
    $bitis_tarih = 'Bilgi Yok';
}
$pdf->SetXY(57, 215); // X ve Y konumu
$pdf->Cell(0, 6, '' . $bitis_tarih, 0, 0, 'L');

$pdf->SetXY(30, 225);
$pdf->Cell(0, 6, 'Adı, Soyadı, Kaşe, İmza      :', 0, 0, 'L');
$pdf->Line(30, 230, 80, 230); // X=10,Y=50 den X=200,Y=50 e düz çizgi






$pdf->Image('resimler/Genel_Antet_Alt_Firuzkoy.jpg', 30, 269, 155); // X=15mm, Y=10mm, Genişlik=50mm


if ($teklif) {
    $pdf->Write(100, "Teklif Detayları:\n\n");

    foreach ($teklif as $alan => $deger) {
        // Tarih formatı varsa biçimlendir
        if (strtotime($deger) !== false && strlen($deger) >= 8) {
            $deger = date('d/m/Y', strtotime($deger));
        }
        $pdf->Write(0, ucfirst(str_replace('_', ' ', $alan)) . ": " . $deger . "\n");
    }
} else {
    $pdf->Write(0, "Teklif bilgisi bulunamadı.");
}

$pdf->Output('teklif_' . $teklif_id . '.pdf', 'I');
exit;
