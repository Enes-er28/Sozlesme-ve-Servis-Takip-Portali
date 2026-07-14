<?php
require_once __DIR__ . '/../data/class.php';

$admin = new AdminClass();

// teklif_id dışarıdan alalım
$teklif_id = isset($_GET['teklif_id']) ? (int)$_GET['teklif_id'] : 0;
if ($teklif_id <= 0) die("Geçersiz teklif ID");

// Teklif bilgilerini al
$teklif = $admin->getTeklifDetay($teklif_id);
if (!$teklif) die("Teklif bulunamadı");

// XML için sabitler
$DepoNo        = 0;   
$SatisFiyatTur = 1;   
$usd_kur       = 32;  
$gun_tarih     = date("d.m.Y"); // Örn: 22.08.2025
date_default_timezone_set("Europe/Istanbul");

// Hareketler için TRANSACTION döngüsü
$xml_transactions = "";
foreach ($teklif['hareketler'] as $satir) {
    $CODE        = htmlspecialchars($satir['logo_kod'], ENT_QUOTES | ENT_XML1, 'UTF-8');
    $Miktar      = $satir['miktar'];
    $BirimAd     = "ADET";
    $br_tutar_tl = $satir['fiyat'];
    $kdv         = $satir['kdv'];
    $type        = ($satir['marka_ad'] === 'Hizmet') ? 4 : 0;

    $xml_transactions .= "
<TRANSACTION>
    <TYPE>$type</TYPE>
    <MASTER_CODE>$CODE</MASTER_CODE>
    <QUANTITY>$Miktar</QUANTITY>
    <PRICE>$br_tutar_tl</PRICE>
    <VAT_RATE>$kdv</VAT_RATE>
    <UNIT_CODE>$BirimAd</UNIT_CODE>
    <EDT_CURR>160</EDT_CURR>
</TRANSACTION>";
}

// ====== Fatura üst bilgileri ======
$ftr_no        = "TF-" . $teklif['teklif_id'];
$bedelsiz      = "DOC-" . $teklif['teklif_id'];
$TIME_HESAP    = date("His");
$CariKod       = htmlspecialchars($teklif['firma_logo_kod'], ENT_QUOTES | ENT_XML1, 'UTF-8'); // Firma logo kodu
$note1         = $teklif['firma_ad'];
$note2         = $teklif['yetkili'];
$saat          = date("H");
$dakika        = date("i");
$usd_kur       = "30.00";
$doviz_kur     = "1.00";
$cursellTotals = "1";
$cursellDetails= "1";
$SatisFiyatTur = "1"; // TL

// ====== XML dosya oluşturma ======
$sd = date('His');
$dosya = "SatisFatura$sd.xml";
$dosya_yol = "LogoXmlImport/Xml_Dosyalar/$dosya";

if (!is_dir(dirname($dosya_yol))) {
    mkdir(dirname($dosya_yol), 0777, true); // Klasör yoksa oluştur
}

$xml_dosya_fis = fopen($dosya_yol, 'wb');
if (!$xml_dosya_fis) die("Dosya açılamadı: $dosya_yol");

// UTF-8 BOM ekle
fwrite($xml_dosya_fis, "\xEF\xBB\xBF");

// XML başlığı
$fis_ac = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<SALES_INVOICES>";
fwrite($xml_dosya_fis, $fis_ac . "\r\n");

// XML içerik
$xml_fis_ust = "<INVOICE DBOP=\"INS\">
    <TYPE>8</TYPE>
    <NUMBER>$ftr_no</NUMBER>
    <DATE>$gun_tarih</DATE>
    <DOC_NUMBER></DOC_NUMBER>
    <TIME>$TIME_HESAP</TIME>
    <ARP_CODE>$CariKod</ARP_CODE>
    <CURRSEL_TOTALS>2</CURRSEL_TOTALS>
    <DISPATCHES>
        <DISPATCH>
            <TYPE>8</TYPE>
            <NUMBER>~</NUMBER>
            <DATE>$gun_tarih</DATE>
            <TIME>$TIME_HESAP</TIME>
            <DOC_NUMBER></DOC_NUMBER>
            <INVOICE_NUMBER>$ftr_no</INVOICE_NUMBER>
            <ARP_CODE>$CariKod</ARP_CODE>
            <DATE_CREATED>$gun_tarih</DATE_CREATED>
            <CURRSEL_TOTALS>2</CURRSEL_TOTALS>
            <CURR_TRANSACTION>0</CURR_TRANSACTION>
        </DISPATCH>
    </DISPATCHES>
    <TRANSACTIONS>
        $xml_transactions
    </TRANSACTIONS>
    <DOC_DATE>$gun_tarih</DOC_DATE>
</INVOICE>
</SALES_INVOICES>";

// Dosyaya yaz
fwrite($xml_dosya_fis, $xml_fis_ust . "\r\n");
fclose($xml_dosya_fis);

echo "XML dosyası oluşturuldu: $dosya_yol";
?>
