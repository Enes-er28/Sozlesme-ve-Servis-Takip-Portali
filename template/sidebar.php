<style>


/* Normal link */
.nav-link {
  color: #444444 !important;
  transition: color 0.3s ease;
}

/* Hover */
.nav-link:hover {
  color: #22c0ffff !important;
}

/* Aktif link */
.nav-link.active {
  color: #ffffffff !important;
  font-weight: 600;
  background-color: #464646c9 !important;
}
/* Varsayılan sidebar genişliği */
.main-sidebar {
  width: 250px;
  font-size: 14px;
  transition: width 0.3s ease, font-size 0.3s ease;
}

/* Küçük ekranlarda sidebar ve içindeki yazılar küçülür */
@media (max-width: 1200px) {
  .main-sidebar {
    width: 180px !important;
    font-size: 10px !important;
  }
  .main-sidebar .brand-link,
  .main-sidebar .nav-link,
  .main-sidebar .user-panel .info {
    font-size: 10px !important;
  }
}


@media (max-width: 768px) {
  .main-sidebar {
    width: 110px !important;
    font-size: 10px !important;
  }
  .main-sidebar .brand-link,
  .main-sidebar .nav-link,
  .main-sidebar .user-panel .info {
    font-size: 10px !important;
  }
}

@media (max-width: 480px) {
  .main-sidebar {
    width: 100px !important;
    font-size: 10px !important;
  }
  .main-sidebar .brand-link,
  .main-sidebar .nav-link,
  .main-sidebar .user-panel .info {
    font-size: 10px !important;
  }
}


</style>

<body class="hold-transition sidebar-mini sidebar-collapse layout-footer-fixed">

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-primary elevation-4">
  <!-- Brand Logo -->
  <a href="index.php" class="brand-link">
    <img src="resimler/ER_LOGO_RESİM.jpg" alt="Logo" 
             style="width:60px; height:auto; border-radius:0; position:absolute; top:10px; left:10px;">

    <span class="brand-text font-weight"></span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">

    <!-- Kullanıcı Paneli -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="info">
          <?php
            if (isset($_SESSION['isim'])) {
              echo htmlspecialchars($_SESSION['isim']);
            } else {
              echo "Giriş yapılmamış";
            }
          ?>

      </div>
    </div>

    <!-- Arama Kutusu -->
    <div class="form-inline">
      <div class="input-group" data-widget="sidebar-search">
        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
        <div class="input-group-append">
          <button class="btn btn-sidebar">
            <i class="fas fa-search fa-fw"></i>
          </button>
        </div>
      </div>
    </div>

    <nav class="mt-2">
  <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">

  
    <!-- Hizmetler ve Destek -->
     <li class="nav-item">
      <a href="./teklif_ekleme.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Teklif Oluştur</p>
      </a>
      <li class="nav-item">
      <a href="./teklif_bekleyen.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Bekleyen Teklifler</p>
      </a>
    </li> 
    <li class="nav-item">
      <a href="./teklif_genel.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Bütün Teklifler</p>
      </a>
    </li> 
    <li class="nav-item">
      <a href="./Firma.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Cari Kartlar</p>
      </a>
    </li> 
    <li class="nav-item">
      <a href="./Sube.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Şubeler</p>
      </a>
    </li> 
    <li class="nav-item">
      <a href="./marka.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Markalar</p>
      </a>
    </li> 
    <li class="nav-item">
      <a href="./model.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Modeller</p>
      </a>
    </li> 
    <li class="nav-item">
      <a href="./kullanicilar.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Kullanıcılar</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="./ekle_cihaz.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Cihaz Ekle</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="./ekle_isletim_sistem.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>İşletim Sistemi Ekle</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="./pasife_al.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Pasif'e Alma
        </p>
      </a>
    </li>
    <li class="nav-item">
      <a href="./excel_list.php" class="nav-link">
        <i class="nav-icon fas fa-users"></i>
        <p>Bilgi Tabloları
        </p>
      </a>
    </li>

  </ul>
</nav>

      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    $(document).ready(function(){
      if (!$('body').hasClass('sidebar-collapse')) {
        $('body').addClass('sidebar-collapse');
      }
    });
  </script>
</body>