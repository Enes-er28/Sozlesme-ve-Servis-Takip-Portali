<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Radar Test Kumandası (ID: 1)</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 30px; background: #f4f6f9;">
    <h2>📡 CTI Radar Test Kumandası (ID Tabanlı)</h2>
    <p>Aşağıdaki butonlar <b>Kullanıcı ID: 1</b> üzerinden sistemi test eder:</p>

    <button onclick="testEt('ping_at', {kullanici_id: 1, cihaz_tipi: 'pc'})" 
            style="padding:12px; margin-bottom:10px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer; width: 300px;">
        1. PC'den Sinyal (Ping) Gönder (ID: 1)
    </button><br>

    <button onclick="testEt('cagri_geldi', {numara: '05554443322', kullanici_id: 1})" 
            style="padding:12px; margin-bottom:10px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer; width: 300px;">
        2. Sahte Telefon Çağrısı (ID: 1'e Gönder)
    </button><br>

    <button onclick="testEt('cagri_kontrol', {kullanici_id: 1})" 
            style="padding:12px; margin-bottom:10px; background:#dc3545; color:white; border:none; border-radius:5px; cursor:pointer; width: 300px;">
        3. Radarı Çalıştır (ID: 1'de Bildirim Var mı?)
    </button><br>

    <hr style="margin: 20px 0;">
    <h3>API'den Gelen Cevap:</h3>
    <pre id="sonucEkran" style="background:#222; color:#0f0; padding:15px; border-radius:5px; font-size:16px;">Bekleniyor...</pre>

    <script>
        function testEt(islem, veri) {
            let formData = new URLSearchParams();
            for (let key in veri) { formData.append(key, veri[key]); }

            // Dosya yolu: data/api_cagri.php
            fetch('data/api_cagri.php?islem=' + islem, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('sonucEkran').innerText = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('sonucEkran').innerText = "HATA: " + error + "\n(Dosya yolu yanlış olabilir mi?)";
            });
        }
    </script>
</body>
</html>