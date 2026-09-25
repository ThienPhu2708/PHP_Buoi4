<?php
require 'connect_labdb.php';

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->execute([$_GET['id']]);
}

header("Location: bai5.php");
exit;
?>


