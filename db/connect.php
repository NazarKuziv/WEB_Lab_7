<?php
$host = 'localhost';
$db = 'library3';
$user = 'root';
$password = '';

$dsn = "mysql:host=$host;dbname=$db;charset=UTF8";


try {
$pdo = new PDO($dsn, $user, $password);

if ($pdo) {
  $conn = mysqli_connect($host, $user, $password, $db);
  error_log("[--Connected to the $db database successfully!--]<br>");
  return $pdo;
 
}
} catch (PDOException $e) {
echo $e->getMessage();
}

?>