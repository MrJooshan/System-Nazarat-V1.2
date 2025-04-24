<?php
$host = 'localhost'; // یا آدرس سرور پایگاه داده
$db = 'sz';
$user = 'root';
$pass = '';

$connection = mysqli_connect($host, $user, $pass, $db);

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully";
?>
