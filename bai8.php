<?php
require 'connect_labdb.php';

$message = '';
$messageType = 'success';

try {
    $checkCol = $conn->query("SHOW COLUMNS FROM students LIKE 'birthday'");
    if ($checkCol->rowCount() == 0) {
        $conn->exec("ALTER TABLE students ADD COLUMN birthday DATE NULL");
        $message = "Đã tự động thêm cột 'birthday' (DATE) vào bảng students thành công!";
    }
} catch (PDOException $e) {
    $message = "Lỗi khi kiểm tra/thêm cột: " . $e->getMessage();
    $messageType = 'danger';
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_single') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $birthday = !empty($_POST['birthday']) ? $_POST['birthday'] : null;

    if ($id > 0) {
        try {
            $stmtUpdate = $conn->prepare("UPDATE students SET birthday = ? WHERE id = ?");
            $stmtUpdate->execute([$birthday, $id]);
            $message = "Cập nhật ngày sinh cho sinh viên ID #$id thành công!";
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = "Lỗi khi cập nhật ngày sinh: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_sample_all') {
    try {
        $stmtNull = $conn->query("SELECT id FROM students WHERE birthday IS NULL");
        $nullStudents = $stmtNull->fetchAll(PDO::FETCH_ASSOC);

        $sampleDates = ['2003-05-15', '2004-08-20', '2003-11-10', '2004-02-28', '2005-09-02'];
        $count = 0;
        $stmtUpdate = $conn->prepare("UPDATE students SET birthday = ? WHERE id = ?");
        foreach ($nullStudents as $idx => $s) {
            $date = $sampleDates[$idx % count($sampleDates)];
            $stmtUpdate->execute([$date, $s['id']]);
            $count++;
        }
        $message = "Đã cập nhật ngày sinh mẫu cho $count sinh viên chưa có ngày sinh!";
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = "Lỗi cập nhật mẫu: " . $e->getMessage();
        $messageType = 'danger';
    }
}
$stmt = $conn->prepare("SELECT * FROM students ORDER BY id ASC");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$editStudent = null;
if ($editId > 0) {
    $stmtFind = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmtFind->execute([$editId]);
    $editStudent = $stmtFind->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài 08: Thêm cột birthday và cập nhật dữ liệu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4 mb-5">
    <h2 class="mb-3">Bài tập 08: Thêm cột birthday và Cập nhật dữ liệu</h2>
    <p class="text-muted">Yêu cầu: Thêm cột <code>birthday</code> vào bảng <code>students</code> và cập nhật dữ liệu ngày sinh.</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Cập nhật Ngày sinh (Birthday)</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_single">

                        <div class="mb-3">
                            <label class="form-label">Chọn sinh viên:</label>
                            <select name="id" class="form-select" required>
                                <option value="">-- Chọn sinh viên --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($editStudent && $editStudent['id'] == $s['id']) ? 'selected' : '' ?>>
                                        #<?= $s['id'] ?> - <?= htmlspecialchars($s['name']) ?> (<?= !empty($s['birthday']) ? $s['birthday'] : 'Chưa có ngày sinh' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ngày sinh (birthday):</label>
                            <input type="date" name="birthday" class="form-control"
                                   value="<?= $editStudent && !empty($editStudent['birthday']) ? $editStudent['birthday'] : '' ?>" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Lưu ngày sinh</button>
                    </form>

                    <hr>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_sample_all">
                        <button type="submit" class="btn btn-outline-info w-100" onclick="return confirm('Tự động điền ngày sinh mẫu cho các bạn chưa có?')">
                            Điền ngày sinh mẫu cho sinh viên chưa có
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="card-title mb-0">Danh sách sinh viên (Bảng students)</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Họ và tên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Ngày sinh (Birthday)</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students): ?>
                                <?php foreach ($students as $row): ?>
                                    <tr class="<?= ($editId == $row['id']) ? 'table-warning' : '' ?>">
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td><?= htmlspecialchars($row['phone']) ?></td>
                                        <td>
                                            <?php if (!empty($row['birthday'])): ?>
                                                <span class="badge bg-success"><?= date('d/m/Y', strtotime($row['birthday'])) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Chưa cập nhật</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="bai8.php?edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                Chọn sửa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Chưa có sinh viên nào</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
