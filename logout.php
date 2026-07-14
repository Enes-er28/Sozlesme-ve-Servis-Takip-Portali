<?php
session_start();
ob_start();
session_destroy();
unset($_SESSION['kullanici_adi']);
unset($_SESSION['login']);
header('location: ./index.php');
