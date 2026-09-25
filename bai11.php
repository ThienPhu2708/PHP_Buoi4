<?php
require 'connect_labdb.php';

$allowedColumns = ['id', 'name', 'email', 'phone', 'birthday'];
$allowedOrders = ['ASC', 'DESC'];

$sortBy = isset($_GET['sort']) && in_array($_GET['sort'], $allowedColumns) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && in_array(strtoupper($_GET['order']), $allowedOrders) ? strtoupper($_GET['order']) : 'ASC';

$nextOrder = ($order === 'ASC') ? 'DESC' : 'ASC';

$sql = "SELECT * FROM students ORDER BY {$sortBy} {$order}";
$stmt = $conn->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

function renderSortLink($column, $title, $currentSort, $currentOrder, $nextOrder) {
    $active = ($currentSort === $column);
    $icon = '';
    if ($active) {
        $icon = ($currentOrder === 'ASC') ? ' ▲' : ' ▼';
    }
    $urlOrder = $active ? $nextOrder : 'ASC';
    return "<a href=\"bai11.php?sort={$column}&order={$urlOrder}\" class=\"text-decoration-none text-dark d-block\">
                <strong>{$title}</strong><span class=\"text-primary\">{$icon}</span>
            </a>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 11: Sắp xếp danh sách theo tên hoặc email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5">
    <h2 class="mb-2">Bài tập 11: Sắp xếp danh sách sinh viên theo tên hoặc email</h2>
    <p class="text-muted">Nhấn vào tiêu đề các cột <strong>Họ tên</strong> hoặc <strong>Email</strong> (hoặc chọn dropdown) để sắp xếp tăng dần / giảm dần.</p>
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="col-form-label"><strong>Sắp xếp theo:</strong></label>
                </div>
                <div class="col-auto">
                    <select name="sort" class="form-select">
                        <option value="id" <?= ($sortBy === 'id') ? 'selected' : '' ?>>ID (Mặc định)</option>
                        <option value="name" <?= ($sortBy === 'name') ? 'selected' : '' ?>>Họ và tên</option>
                        <option value="email" <?= ($sortBy === 'email') ? 'selected' : '' ?>>Email</option>
                        <option value="birthday" <?= ($sortBy === 'birthday') ? 'selected' : '' ?>>Ngày sinh</option>
                    </select>
                </div>
                <div class="col-auto">
                    <select name="order" class="form-select">
                        <option value="ASC" <?= ($order === 'ASC') ? 'selected' : '' ?>>Tăng dần (A → Z, cũ → mới)</option>
                        <option value="DESC" <?= ($order === 'DESC') ? 'selected' : '' ?>>Giảm dần (Z → A, mới → cũ)</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Áp dụng</button>
                    <a href="bai11.php" class="btn btn-outline-secondary">Mặc định</a>
                </div>
                <div class="col-auto ms-auto">
                    <span class="badge bg-info text-dark">
                        Đang xếp theo: <strong><?= strtoupper($sortBy) ?></strong> (<?= $order ?>)
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 80px;"><?= renderSortLink('id', 'ID', $sortBy, $order, $nextOrder) ?></th>
                    <th><?= renderSortLink('name', 'Họ và tên', $sortBy, $order, $nextOrder) ?></th>
                    <th><?= renderSortLink('email', 'Email', $sortBy, $order, $nextOrder) ?></th>
                    <th>Số điện thoại</th>
                    <th><?= renderSortLink('birthday', 'Ngày sinh', $sortBy, $order, $nextOrder) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students): ?>
                    <?php foreach ($students as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td>
                                <?= !empty($row['birthday']) ? date('d/m/Y', strtotime($row['birthday'])) : '<span class="text-muted">Chưa cập nhật</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Chưa có dữ liệu</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>


