<?php
include 'db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
    } else {
        echo "نام کاربری یا رمز عبور نادرست است.";
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <title>ورود</title>
</head>
<body>
    <div class="container-lg mt-5">
        <h1 class="text-center mb-4">ورود</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">نام کاربری</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">رمز عبور</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">ورود</button>
        </form>
        <p class="mt-3">هنوز ثبت‌نام نکرده‌اید؟ <a href="register.php">ثبت‌نام کنید</a></p>
    </div>
</body>
</html>