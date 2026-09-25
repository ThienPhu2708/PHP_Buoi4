<?php
require 'connect_labdb.php';

// Lấy từ khóa tìm kiếm theo tên
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Thực hiện truy vấn tìm kiếm bằng Prepared Statement
if ($keyword !== '') {
    $stmt = $conn->prepare("SELECT * FROM students WHERE name LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$keyword%"]);
} else {
    // Nếu không nhập từ khóa, lấy toàn bộ danh sách
    $stmt = $conn->prepare("SELECT * FROM students ORDER BY id DESC");
    $stmt->execute();
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalFound = count($students);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 09: Tìm kiếm sinh viên theo tên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5">
    <h2 class="mb-3">Bài tập 09: Tìm kiếm sinh viên theo tên</h2>
    <p class="text-muted">Chức năng tìm kiếm sinh viên theo tên sử dụng <strong>Prepared Statement</strong> chống SQL Injection.</p>

    <!-- Form tìm kiếm theo tên -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                               class="form-control" placeholder="Nhập tên sinh viên cần tìm (VD: Nam, Linh, Khoa...)">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                    <?php if ($keyword !== ''): ?>
                        <a href="bai9.php" class="btn btn-outline-secondary">Xem tất cả</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Thông báo kết quả tìm kiếm -->
    <?php if ($keyword !== ''): ?>
        <?php if ($totalFound > 0): ?>
            <div class="alert alert-success">
                Tìm thấy <strong><?= $totalFound ?></strong> sinh viên phù hợp với từ khóa: <em>"<?= htmlspecialchars($keyword) ?>"</em>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                Không tìm thấy sinh viên nào có tên chứa từ khóa: <em>"<?= htmlspecialchars($keyword) ?>"</em>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-secondary">Đang hiển thị toàn bộ danh sách: <strong><?= $totalFound ?></strong> sinh viên</p>
    <?php endif; ?>

    <!-- Bảng kết quả -->
    <table class="table table-bordered table-hover table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Ngày sinh</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($totalFound > 0): ?>
                <?php foreach ($students as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php
                            if ($keyword !== '') {
                                // Highlight từ khóa tìm kiếm trong tên
                                echo preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<mark class="bg-warning">$1</mark>', htmlspecialchars($row['name']));
                            } else {
                                echo htmlspecialchars($row['name']);
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td>
                            <?= !empty($row['birthday']) ? date('d/m/Y', strtotime($row['birthday'])) : '<span class="text-muted">Chưa cập nhật</span>' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Không có dữ liệu phù hợp</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
