<?php
require 'connect_labdb.php';

$stmt = $conn->query("SELECT* FROM STUDENTS");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h4>Bai 6. Hien thi danh sach sinh vien tu labdb</h2>
<table border ="1" cellpadding="5">
    <tr>
        <td>ID</td>
        <td>Name</td>
        <td>Email</td>
        <td>Phone Number</td>
        <td>Action</td>
    </tr>

    <?php foreach($students as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['email'] ?></td>
            <td><?= $row['phone'] ?></td>
            <td>
                <a href="delete_student.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">
                    Delete
                </a>
            /
                <a href="edit_student.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">
                    Edit
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>