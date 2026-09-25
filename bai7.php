<?php
require 'connect_labdb.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$limit = 5; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$sqlCount = "SELECT COUNT(*) FROM students WHERE name LIKE :keyword";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->execute([':keyword' => "%$keyword%"]);
$totalRecords = $stmtCount->fetchColumn();
$totalPages = ceil($totalRecords / $limit);
if ($totalPages < 1) $totalPages = 1;

$sql = "SELECT * FROM students 
        WHERE name LIKE :keyword
        ORDER BY id DESC
        LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':keyword', "%$keyword%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 07: Phân trang và Tìm kiếm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5">
    <h2 class="mb-4">Bài tập 07: Phân trang và Tìm kiếm</h2>
    <form method="get" class="row mb-3">
        <div class="col-md-4">
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                   class="form-control" placeholder="Nhập tên cần tìm">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            <?php if (!empty($keyword)): ?>
                <a href="bai7.php" class="btn btn-outline-secondary">Xem tất cả</a>
            <?php endif; ?>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($students): ?>
                <?php foreach ($students as $index => $row): ?>
                    <tr>
                        <td><?= $offset + $index + 1 ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted">Không có dữ liệu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
    <nav>
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=1">Đầu</a></li>
                <li class="page-item"><a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">Trước</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">Sau</a></li>
                <li class="page-item"><a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $totalPages ?>">Cuối</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</body>
</html>
