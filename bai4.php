<!-- Them thong tin sinh vien tu form -->
<?php
    require 'connect_labdb.php';
    if($_SERVER['REQUEST_METHOD']=='POST'){
        $stmt=$conn->prepare("INSERT INTO STUDENTS(NAME, EMAIL, PHONE) VALUES(?,?,?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['phone']
        ]);

        echo "Add Successfully";

    }
?>
<h4>Form Add data</h4>
<form method="POST">
    <label>Name:</label><input type="text" name="name" required><br>
    <label>Email</label><input type="text" name="email" required><br>
    <label>Phone Number</label><input type="text" name="phone"><br>

    <button type="submit">Add Student</button>
</form>

