<?php
ob_start(); 
require 'connect_labdb.php';

$message = '';
$messageType = 'success';

$editStudent = null;

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        // [Prepared Statement cho DELETE]
        $stmtDelete = $conn->prepare("DELETE FROM students WHERE id = ?");
        $stmtDelete->execute([$deleteId]);

        header("Location: bai10.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        $message = "Lỗi khi xóa sinh viên: " . $e->getMessage();
        $messageType = 'danger';
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $birthday = !empty($_POST['birthday']) ? $_POST['birthday'] : null;

    if ($name === '' || $email === '') {
        $message = "Vui lòng nhập đầy đủ Họ tên và Email!";
        $messageType = 'warning';
    } else {
        if ($id > 0) {
            // [Prepared Statement cho UPDATE]
            try {
                $stmtUpdate = $conn->prepare("UPDATE students SET name = ?, email = ?, phone = ?, birthday = ? WHERE id = ?");
                $stmtUpdate->execute([$name, $email, $phone, $birthday, $id]);

                header("Location: bai10.php?msg=updated");
                exit;
            } catch (PDOException $e) {
                $message = "Lỗi khi cập nhật sinh viên: " . $e->getMessage();
                $messageType = 'danger';
            }
        } else {
            // [Prepared Statement cho INSERT]
            try {
                $stmtInsert = $conn->prepare("INSERT INTO students (name, email, phone, birthday) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([$name, $email, $phone, $birthday]);

                header("Location: bai10.php?msg=added");
                exit;
            } catch (PDOException $e) {
                $message = "Lỗi khi thêm sinh viên: " . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}

// Nhận thông báo từ URL chuyển hướng
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'added') $message = "Thêm mới sinh viên thành công (bằng Prepared Statement)!";
    if ($_GET['msg'] === 'updated') $message = "Cập nhật sinh viên thành công (bằng Prepared Statement)!";
    if ($_GET['msg'] === 'deleted') $message = "Đã xóa sinh viên thành công (bằng Prepared Statement)!";
    $messageType = 'success';
}

if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $stmtFind = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmtFind->execute([$editId]);
    $editStudent = $stmtFind->fetch(PDO::FETCH_ASSOC);
}

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword !== '') {
    // [Prepared Statement cho SELECT có tìm kiếm]
    $stmtSelect = $conn->prepare("SELECT * FROM students WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC");
    $stmtSelect->execute(["%$keyword%", "%$keyword%"]);
} else {
    // [Prepared Statement cho SELECT toàn bộ danh sách (thay thế $conn->query())]
    $stmtSelect = $conn->prepare("SELECT * FROM students ORDER BY id DESC");
    $stmtSelect->execute();
}
$students = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 10: Chuyển toàn bộ truy vấn sang Prepared Statement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5">
    <h2 class="mb-2">Bài tập 10: Quản lý sinh viên với 100% Prepared Statement</h2>
    <p class="text-muted">Chuyển toàn bộ các thao tác (SELECT, INSERT, UPDATE, DELETE) sang sử dụng <code>prepare()</code> và <code>execute()</code> để bảo mật chống SQL Injection.</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- FORM THÊM / SỬA SINH VIÊN -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-<?= $editStudent ? 'warning' : 'primary' ?>">
                <div class="card-header bg-<?= $editStudent ? 'warning text-dark' : 'primary text-white' ?>">
                    <h5 class="card-title mb-0">
                        <?= $editStudent ? 'Cập nhật sinh viên #' . $editStudent['id'] : '➕ Thêm sinh viên mới' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="bai10.php">
                        <input type="hidden" name="id" value="<?= $editStudent ? $editStudent['id'] : '0' ?>">

                        <div class="mb-3">
                            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= $editStudent ? htmlspecialchars($editStudent['name']) : '' ?>"
                                   placeholder="Nguyễn Văn A">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= $editStudent ? htmlspecialchars($editStudent['email']) : '' ?>"
                                   placeholder="a@example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control"
                                   value="<?= $editStudent ? htmlspecialchars($editStudent['phone']) : '' ?>"
                                   placeholder="0987654321">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ngày sinh</label>
                            <input type="date" name="birthday" class="form-control"
                                   value="<?= ($editStudent && !empty($editStudent['birthday'])) ? $editStudent['birthday'] : '' ?>">
                        </div>

                        <button type="submit" class="btn btn-<?= $editStudent ? 'warning' : 'success' ?> w-100">
                            <?= $editStudent ? 'Lưu thay đổi (UPDATE)' : 'Thêm sinh viên (INSERT)' ?>
                        </button>

                        <?php if ($editStudent): ?>
                            <a href="bai10.php" class="btn btn-outline-secondary w-100 mt-2">Hủy chỉnh sửa</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- BẢNG DANH SÁCH & TÌM KIẾM -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Danh sách sinh viên</h5>
                    <span class="badge bg-light text-dark">Tổng: <?= count($students) ?></span>
                </div>
                <div class="card-body">
                    <!-- Form tìm kiếm bằng Prepared Statement -->
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-8">
                            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                                   class="form-control" placeholder="Tìm theo tên hoặc email...">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary">Tìm kiếm</button>
                            <?php if ($keyword !== ''): ?>
                                <a href="bai10.php" class="btn btn-outline-secondary">Tất cả</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Bảng dữ liệu -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Họ và tên</th>
                                    <th>Email</th>
                                    <th>SĐT</th>
                                    <th>Ngày sinh</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($students): ?>
                                    <?php foreach ($students as $row): ?>
                                        <tr class="<?= ($editStudent && $editStudent['id'] == $row['id']) ? 'table-warning' : '' ?>">
                                            <td><?= $row['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= htmlspecialchars($row['phone']) ?></td>
                                            <td>
                                                <?= !empty($row['birthday']) ? date('d/m/Y', strtotime($row['birthday'])) : '<span class="text-muted small">Chưa có</span>' ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="bai10.php?edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning">
                                                    Sửa
                                                </a>
                                                <a href="bai10.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên ID #<?= $row['id'] ?> không?')">
                                                    Xóa
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Không tìm thấy sinh viên nào</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Bảng đối chiếu các câu lệnh Prepared Statement -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-dark">
                    <strong>Bảng đối chiếu Prepared Statement trong bài 10:</strong>
                </div>
                <div class="card-body p-2">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item"><strong>SELECT All:</strong> <code>$conn->prepare("SELECT * FROM students ORDER BY id DESC")->execute()</code></li>
                        <li class="list-group-item"><strong>SELECT By ID:</strong> <code>$conn->prepare("SELECT * FROM students WHERE id = ?")->execute([$id])</code></li>
                        <li class="list-group-item"><strong>SELECT Search:</strong> <code>$conn->prepare("SELECT * FROM students WHERE name LIKE ? OR email LIKE ?")->execute(["%$kw%", "%$kw%"])</code></li>
                        <li class="list-group-item"><strong>INSERT:</strong> <code>$conn->prepare("INSERT INTO students (name, email, phone, birthday) VALUES (?, ?, ?, ?)")->execute([...])</code></li>
                        <li class="list-group-item"><strong>UPDATE:</strong> <code>$conn->prepare("UPDATE students SET name=?, email=?, phone=?, birthday=? WHERE id=?")->execute([...])</code></li>
                        <li class="list-group-item"><strong>DELETE:</strong> <code>$conn->prepare("DELETE FROM students WHERE id = ?")->execute([$id])</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
