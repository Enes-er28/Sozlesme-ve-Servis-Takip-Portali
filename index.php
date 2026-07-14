<?php include_once 'template/header.php'; ?>


  <?php
if (isset($_GET['route'])) {
  $route = strtolower($_GET['route']);
$route = str_replace('.php', '', $route);
$pages = 'pages/' . $route . '.php';


} else {
  $pages = 'null';
}

if (file_exists($pages)) {
  include_once $pages;
} else {
  include_once 'pages/index.php';
}
?>



<?php include_once 'template/footer.php'; ?>
