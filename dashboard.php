<?php
session_start();
include 'db.php';

// بررسی احراز هویت کاربر
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];

if ($role === 'admin') {
    $role_fa = 'مدیر';
} else {
    $role_fa = 'کاربر';
}

// ارسال نظر
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment']) && $role === 'member') {
    $comment = trim($_POST['comment']);
    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, comment, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$user_id, $comment]);
        $_SESSION['success_message'] = "نظر شما با موفقیت ثبت شد.";
        header("Location: dashboard.php");
        exit();
    }
}

// حذف نظر (برای ادمین)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_comment']) && $role === 'admin') {
    $comment_id = $_POST['comment_id'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    $_SESSION['success_message'] = "نظر با موفقیت حذف شد.";
    header("Location: dashboard.php");
    exit();
}

// دریافت نظرات با امکان صفحه‌بندی
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// تعداد کل نظرات برای صفحه‌بندی
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
$total_pages = ceil($total_comments / $limit);

$comments = $pdo->query("
    SELECT comments.*, users.username 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    ORDER BY comments.created_at DESC 
    LIMIT $limit OFFSET $offset
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد | سیستم نظرات</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .comment-card {
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }
        .comment-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .response-card {
            background-color: #f8f9fa;
            border-right: 4px solid #6c757d;
        }
        .response-form {
            display: none;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">سیستم نظرات</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">داشبورد</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white me-2"><?php echo htmlspecialchars($username); ?></span>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header"><?php echo htmlspecialchars(string: $role_fa); ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">خروج</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if ($role === 'member'): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0"><i class="bi bi-chat-square-text me-2"></i>ارسال نظر جدید</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="comment" class="form-label">متن نظر</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="3" required placeholder="نظر خود را اینجا بنویسید..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> ارسال نظر
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header bg-white">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-chat-left-text me-2"></i>
                            <?php echo $role === 'admin' ? 'همه نظرات' : 'نظرات شما'; ?>
                            <span class="badge bg-secondary"><?php echo $total_comments; ?></span>
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($comments)): ?>
                            <div class="alert alert-info">هنوز نظری ثبت نشده است.</div>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="list-group-item p-0 mb-3 border-0">
                                        <div class="comment-card card mb-2">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start">
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h5 class="mb-0"><?php echo htmlspecialchars($comment['username']); ?></h5>
                                                            <small class="text-muted">
                                                                <i class="bi bi-clock me-1"></i>
                                                                <?php echo date('Y/m/d H:i', strtotime($comment['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                                        
                                                        <?php if ($role === 'admin'): ?>
                                                            <div class="d-flex justify-content-end mt-2">
                                                                <button class="btn btn-sm btn-outline-success me-2" onclick="toggleResponseForm(<?php echo $comment['id']; ?>)">
                                                                    <i class="bi bi-reply"></i> پاسخ
                                                                </button>
                                                                <form method="POST" onsubmit="return confirm('آیا از حذف این نظر مطمئن هستید؟');">
                                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                                    <button type="submit" name="delete_comment" class="btn btn-sm btn-outline-danger">
                                                                        <i class="bi bi-trash"></i> حذف
                                                                    </button>
                                                                </form>
                                                            </div>
                                                            
                                                            <div id="response-form-<?php echo $comment['id']; ?>" class="response-form mt-3">
                                                                <form method="POST" action="respond.php">
                                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                                    <div class="mb-2">
                                                                        <label for="response-<?php echo $comment['id']; ?>" class="form-label">پاسخ شما:</label>
                                                                        <textarea class="form-control" id="response-<?php echo $comment['id']; ?>" name="response" rows="3" required></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-success btn-sm">
                                                                        <i class="bi bi-check-circle"></i> ثبت پاسخ
                                                                    </button>
                                                                    <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="toggleResponseForm(<?php echo $comment['id']; ?>)">
                                                                        <i class="bi bi-x-circle"></i> انصراف
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($comment['response'])): ?>
                                            <div class="response-card card ms-5">
                                                <div class="card-body py-2">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-person-check-fill text-success me-2"></i>
                                                        <strong>پاسخ مدیریت:</strong>
                                                        <small class="text-muted ms-2">
                                                            <?php echo date('Y/m/d H:i', strtotime($comment['response_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['response'])); ?></p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- صفحه‌بندی -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleResponseForm(commentId) {
            const form = document.getElementById(`response-form-${commentId}`);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>