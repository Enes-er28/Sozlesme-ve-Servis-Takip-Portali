<?php
include_once 'template/navbar2.php';
include_once 'template/sidebar.php';

$admin = new AdminClass();
$admin->sadece_admin();

$sonucMesaji = null;

if (isset($_POST['pasif_yap'])) {
    $tarih = $_POST['tarih'];

    if (!empty($tarih)) {

        $sql = "
            UPDATE ah
            SET ah.aktif = 0
            FROM abone_hizmet ah
            JOIN (
                SELECT abone_hizmet_id, MIN(abone_hizmet_hareket_id) AS ilk_id
                FROM abone_hizmet_hareket
                GROUP BY abone_hizmet_id
            ) x ON x.abone_hizmet_id = ah.abone_hizmet_id
            JOIN abone_hizmet_hareket hm 
                ON hm.abone_hizmet_hareket_id = x.ilk_id
            WHERE hm.bitis < ?
        ";

        $sonuc = $adminclass->pdoPrepare($sql, [$tarih]);

        if ($sonuc) {
            $sonucMesaji = '<div class="alert alert-success">
                Seçilen tarihten önce biten abonelikler başarıyla pasif yapıldı.
            </div>';
        } else {
            $sonucMesaji = '<div class="alert alert-danger">
                İşlem sırasında hata oluştu.
            </div>';
        }
    }
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Abone Hizmet Pasif Yapma</h1>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="card col-md-6 mx-auto">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white">
                        ⚠️ Tarihe Göre Toplu Pasif Yapma
                    </h3>
                </div>

                <div class="card-body">

                    <?= $sonucMesaji ?>

                    <form method="POST" id="pasifForm">
                        <div class="form-group col-md-6 mx-auto">
                            <label class="text-center d-block font-weight-bold">
                                Bu tarihten ÖNCE biten abonelikler
                            </label>
                            <input type="date" name="tarih" class="form-control text-center" required>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-danger px-5">
                                Pasif Yap
                            </button>
                        </div>

                        <input type="hidden" name="pasif_yap" value="1">
                    </form>

                </div>
            </div>

        </div>
    </section>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.getElementById('pasifForm').addEventListener('submit', function(e) {
    e.preventDefault();

    Swal.fire({
        title: 'Emin misiniz?',
        html: '<b>Bu işlem geri alınamaz!</b><br>Seçilen tarihten önce biten tüm abonelikler pasif yapılacaktır.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Evet, devam et',
        cancelButtonText: 'Vazgeç',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Son Onay',
                text: 'Bu işlemi gerçekten yapmak istiyor musunuz?',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Evet, pasif yap',
                cancelButtonText: 'İptal',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((finalResult) => {
                if (finalResult.isConfirmed) {
                    e.target.submit();
                }
            });
        }
    });
});
</script>

</body>
</html>
