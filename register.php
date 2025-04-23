<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $password, $role])) {
        header("Location: login.php");
    } else {
        echo "خطا در ثبت‌نام.";
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <title>ثبت‌نام</title>
</head>
<body>
    <div class="container-lg mt-5">
        <h1 class="text-center mb-4">ثبت‌نام</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">نام کاربری</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">رمز عبور</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">نقش</label>
                <select class="form-select" id="role" name="role">
                    <option value="member">عضو</option>
                    <option value="admin">مدیر</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">ثبت‌نام</button>
        </form>
        <p class="mt-3">قبلاً ثبت‌نام کرده‌اید؟ <a href="login.php">وارد شوید</a></p>
    </div>
</body>
</html>