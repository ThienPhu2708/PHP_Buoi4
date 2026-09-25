<!-- Hien thi danh sach sinh vien tu labdb -->

<?php
require 'connect_labdb.php';

$stmt = $conn->query("SELECT* FROM STUDENTS");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h4>Bai 3. Hien thi danh sach sinh vien tu labdb</h2>
<table border ="1" cellpadding="5">
    <tr>
        <td>ID</td>
        <td>Name</td>
        <td>Email</td>
        <td>Phone Number</td>
    </tr>
    <?php foreach($students as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['phone'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>
