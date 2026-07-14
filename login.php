<?php
session_start();
ob_start();
include_once 'data/class.kullanicilar.php'; 
$app = new AdminKullaniciClass();

if (isset($_POST['login'])) {
    $kullanici_adi = trim($app->getSecurity($_POST['kullanici_adi']));
    $password      = trim($_POST['sifre']); // sadece trim, getSecurity kullanma

    if (empty($kullanici_adi) || empty($password)) {
        print '<div class="alert alert-danger">Boş alan bırakmayınız...</div>';
    } else {
        $users = $app->getUser($kullanici_adi);
        if (!$users || !is_array($users)) {
            print '<div class="alert alert-danger">Kullanıcı Bulunamadı...</div>';
        } else {
            if (isset($users['sifre']) && password_verify($password, $users['sifre'])) {
                if (!empty($users['durum']) && strtolower($users['durum']) === 'aktif') {
                    // oturum aç
                    $_SESSION['login'] = true;
                    $_SESSION['kullanici_id'] = $users['id'];
                    $_SESSION['kullanici_adi'] = $users['kullanici_adi'];
                    $_SESSION['isim'] = $users['isim'];
                    $_SESSION['rol'] = $users['rol'];
                    $_SESSION['yol'] = !empty($users['yol'])
                    ? $users['yol']
                    : 'C:/xampp/htdocs/Erportal/uploads';
                        if (strtolower($users['rol']) === 'admin') {
                          header("Location: index.php");
                      } else {
                          header("Location: eskidestek.php");
                      }
                      exit;
                  }
                  else {
                    echo "Hesabınız aktif değil. Lütfen yöneticiye başvurun.";
                }
            } else {
                print '<div class="alert alert-danger">Şifre Yanlış...</div>';
            }
        }
    }
}



?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kullanıcı Giriş</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="index.php" class="h1"><b>Yönetim Paneli</b></a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Lütfen Oturum Açınız...</p>

      <form method="post">
        <input type="hidden" name="login" value="1001">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="kullanici_adi" placeholder="Kullanıcı Adı" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" name="sifre" placeholder="Şifre" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Oturum Aç</button>
          </div>
          <!-- /.col -->
        </div>
      </form>

    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
