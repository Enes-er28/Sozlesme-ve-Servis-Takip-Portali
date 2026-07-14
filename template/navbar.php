<?php 
// Oturum başlatılmamışsa başlat (Kullanıcı ID ve İsim için gerekli)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<html lang="tr">
<head>
  <style>
      .wrapper {
    margin-left: 0 !important;
    padding-left: 0 !important;
  }
  .main-header.navbar {
    margin-left: 0 !important;
    padding-left: 0 !important;
    width: 100%;
  }
  /* Sadece sol navbar için sıfırla */
  .navbar-nav:not(.ml-auto) {
    margin-left: 0 !important;
    padding-left: 0 !important;
  }
  </style>
</head>

<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../Erportal/anasayfa.php" class="nav-link">Anasayfa</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../Erportal/destek.php" class="nav-link">Destek</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../Erportal/eskidestek.php" class="nav-link">Tamamlanan Destek Kayıtları</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../Erportal/musteriler.php" class="nav-link">Müşteriler</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../Erportal/envanter.php" class="nav-link">Envanter</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../Erportal/sozlesmeler.php" class="nav-link">Aktif Sözleşmeler</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="../Erportal/sozlesmeler_tum.php" class="nav-link">Tüm Sözleşmeler</a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      
      <li class="nav-item">
        <a class="nav-link" href="#" id="btnManuelPing" title="Telefon Bağlantısını Kontrol Et">
          <i class="fas fa-sync-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" title="Telefon Durumu">
          <i class="fas fa-mobile-alt" id="mobilDurumIkon" style="color: gray; font-size: 20px;"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="./logout.php" class="nav-link">Oturum Kapat</a>
      </li>
    </ul>
  </nav>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery == 'undefined') return;

    const API_YOLU = '/Erportal/data/api_cagri.php';
    const KULLANICI_ID = <?php echo isset($_SESSION['kullanici_id']) ? $_SESSION['kullanici_id'] : 0; ?>;
    
    // Zayıf ihtimal ama bildirimi kaçırmamak için ses ekliyoruz
    const bildirimSesi = new Audio('https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3');

    if (KULLANICI_ID > 0) {
        
        // 1. PING FONKSİYONU (Aynı bıraktım)
        function pingAt() {
            $.post(API_YOLU + '?islem=ping_at', { kullanici_id: KULLANICI_ID, cihaz_tipi: 'pc' }, function(res) {
                if(res.durum === 'basarili') {
                    let ikon = document.getElementById('mobilDurumIkon');
                    if(res.mobil_son_sinyal) {
                        let sonSinyal = new Date(res.mobil_son_sinyal.replace(/-/g, '/')); 
                        let suAn = new Date();
                        let farkDk = (suAn - sonSinyal) / 1000 / 60;
                        ikon.style.color = (farkDk < 6) ? '#28a745' : '#dc3545';
                        ikon.title = (farkDk < 6) ? "Telefon Bağlı" : "Bağlantı Koptu";
                    }
                }
            }, 'json');
        }

        // 2. GELİŞMİŞ ÇAĞRI RADARI (Hata payını sıfırlayan yapı)
        function cagriKontrol() {
            $.ajax({
                url: API_YOLU + '?islem=cagri_kontrol',
                method: 'POST',
                data: { kullanici_id: KULLANICI_ID },
                dataType: 'json',
                timeout: 2500, // İstek 2.5 saniyeden uzun sürerse iptal et
                success: function(res) {
                    if(res.durum === 'cagri_var') {
                        // Ses çal
                        bildirimSesi.play().catch(e => console.log("Ses çalınamadı, etkileşim gerekiyor."));

                        Swal.fire({
                            title: '☎️ Gelen Çağrı!',
                            html: `<b>${res.isim}</b><br><span style="font-size:18px; color:gray;">${res.numara}</span>`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'Yeni Destek Aç',
                            cancelButtonText: 'Yoksay',
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#dc3545',
                            allowOutsideClick: false,
                            timer: 30000 
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open('../Erportal/eskidestek.php?arayan_tel=' + res.numara, '_blank');
                            }
                        });
                    }
                },
                complete: function() {
                    // İstek başarılı ya da başarısız olsun, bittikten 3 saniye sonra yenisini başlat.
                    // Bu sayede asla çakışma olmaz ve veritabanını yormaz.
                    setTimeout(cagriKontrol, 3000);
                }
            });
        }

        // BAŞLAT
        pingAt();
        setInterval(pingAt, 5 * 60 * 1000);
        cagriKontrol(); // İlk tetiklemeyi yap, o kendi kendini besleyecek (complete bloğu sayesinde)

        $('#btnManuelPing').click(function(e) {
            e.preventDefault();
            pingAt();
            let icon = $(this).find('i');
            icon.addClass('fa-spin');
            setTimeout(() => icon.removeClass('fa-spin'), 1000);
        });
    }
});
</script>

</body>
</html>