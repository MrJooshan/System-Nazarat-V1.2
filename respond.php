<?php
session_start();
include 'db.php';

// بررسی احراز هویت و نقش ادمین
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// بررسی وجود داده‌های ارسالی
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_id']) && isset($_POST['response'])) {
    $comment_id = $_POST['comment_id'];
    $response = trim($_POST['response']);
    
    if (!empty($response)) {
        // به روزرسانی پاسخ در دیتابیس
        $stmt = $pdo->prepare("UPDATE comments SET response = ?, response_at = NOW() WHERE id = ?");
        $stmt->execute([$response, $comment_id]);
        
        $_SESSION['success_message'] = "پاسخ شما با موفقیت ثبت شد.";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error_message'] = "لطفاً متن پاسخ را وارد کنید.";
        header("Location: dashboard.php");
        exit();
    }
} else {
    $_SESSION['error_message'] = "درخواست نامعتبر.";
    header("Location: dashboard.php");
    exit();
}
?>