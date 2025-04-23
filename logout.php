<?php
session_start();

// خاتمه دادن به سشن
session_unset();
session_destroy();

// هدایت به صفحه لاگین
header("Location: login.php");
exit();
?>