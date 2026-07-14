<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function destekMailGonder(
    $eposta,
    $firma_adi,
    $musteri_adi,
    $ariza,
    $yapilan_islem,
    $destek_personeli
) {
    if (empty($eposta)) {
        return false;
    }

    try {
        $mail = new PHPMailer(true);

        // SMTP Ayarları
        $mail->isSMTP();
        $mail->Host       = 'mail.erbilgisayar.com.tr';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yanitlamayiniz@erbilgisayar.com.tr';
        $mail->Password   = 'QRFTCW85M@S['; // config dosyasına al
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->CharSet = 'UTF-8';

        // Gönderen
        $mail->setFrom(
            'yanitlamayiniz@erbilgisayar.com.tr',
            'Er Bilgisayar Destek'
        );

        // Alıcı
        $mail->addAddress($eposta);

        // İçerik
        $mail->isHTML(true);
        $mail->Subject = 'Tamamlanan Destek Kaydınız';

        $mail->Body = "
            <p>Merhaba,</p>

            <p>Destek kaydınız başarıyla tamamlanmıştır.</p>

            <table cellpadding='6' cellspacing='0' style='border-collapse:collapse;'>
                <tr>
                    <td><strong>Firma Adı</strong></td>
                    <td>: {$firma_adi}</td>
                </tr>
                <tr>
                    <td><strong>Müşteri Adı</strong></td>
                    <td>: {$musteri_adi}</td>
                </tr>
                <tr>
                    <td><strong>Arıza / Talep</strong></td>
                    <td>: {$ariza}</td>
                </tr>
                <tr>
                    <td><strong>Yapılan İşlem</strong></td>
                    <td>: {$yapilan_islem}</td>
                </tr>
                <tr>
                    <td><strong>Destek Personeli</strong></td>
                    <td>: {$destek_personeli}</td>
                </tr>
                <tr>
                    <td>Bu mail otomatik olarak gönderilmiştir, lütfen yanıtlamayınız.</td>
                </tr>
            </table>

        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Mail Hatası: ' . $mail->ErrorInfo);
        return false;
    }
}
