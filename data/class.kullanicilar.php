<?php
class AdminKullaniciClass {
    protected $pdo = null;
    protected $host = 'ENES-LAPTOP\SQLEXPRESS'; // SQL Server Instance adını buraya yaz
    protected $dbname = 'Erportal'; // MS SQL'deki veritabanı adı
    protected $username = 'sa';
    protected $password = '123';

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "sqlsrv:Server=$this->host;Database=$this->dbname",
                $this->username,
                $this->password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $error) {
            die("SQL Server bağlantı hatası: " . $error->getMessage());
        }

        if (isset($_SESSION['kullanici_adi']) && isset($_SESSION['login'])) {
            header('location: ./index.php');
            exit;
        }
    }


    public function getUser($kullanici_adi) {
        $query = $this->pdo->prepare('SELECT * FROM kullanici WHERE kullanici_adi=?');
        $query->execute([$kullanici_adi]);
        $variable = $query->fetch(PDO::FETCH_ASSOC);
        if ($variable) {
            return $variable;
        }else {return false; }
    }

    public function getSecurity($data) {
        if (is_array($data)) {
            $variable = array_map('htmlspecialchars', $data);
            $response = array_map('stripslashes', $variable);
            return $response;

        } else {
            $variable = htmlspecialchars($data);
            $response = stripslashes($variable);
            return $response;
        }
    }
    public function testUser($kullanici_adi) {
    $stmt = $this->pdo->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi=?");
    $stmt->execute([$kullanici_adi]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



}






?>