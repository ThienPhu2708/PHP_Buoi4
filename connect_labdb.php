<?php
$dbname = "labdb";
$dsn = "mysql:host=localhost;port=3307;dbname=" . $dbname . ";charset=utf8";
$username = "root";
$password = "";
try{
    $conn = new PDO($dsn, $username, $password);
    $conn-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Kết nối thành công voi: " .$dbname ."<p>";
}catch(PDOException $e){
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>

