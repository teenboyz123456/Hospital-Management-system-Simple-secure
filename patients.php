<?php include('db.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Patients</title>
    <style>
        body { font-family: Arial; margin: 0; display: flex; }
        .sidebar { width: 250px; height: 100vh; background: #2c3e50; color: white; padding: 20px; position: fixed; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; }
        .main-content { margin-left: 290px; padding: 30px; width: 100%; }
        input, select { padding: 10px; margin: 10px 0; width: 100%; max-width: 400px; }
        button { padding: 10px 20px; background: #3498db; color: white; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>HMS Admin</h2>
        <a href="index.php">Dashboard</a>
        <a href="doctors.php">Manage Doctors</a>
        <a href="patients.php">Manage Patients</a>
        <a href="appointments.php">Appointments</a>
    </div>

    <div class="main-content">
        <h1>Manage Patients</h1>
        
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3>Register New Patient</h3>
            <form method="POST">
                <input type="text" name="name" placeholder="Full Name" required>
                <input type="number" name="age" placeholder="Age" required>
                <select name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <button type="submit" name="add_patient">Register Patient</button>
            </form>
        </div>

        <?php
        if(isset($_POST['add_patient'])){
            $name = $_POST['name'];
            $age = $_POST['age'];
            $gender = $_POST['gender'];
            $phone = $_POST['phone'];
            $sql = "INSERT INTO patients (name, age, gender, phone) VALUES ('$name', '$age', '$gender', '$phone')";
            mysqli_query($conn, $sql);
            echo "<p style='color:green;'>Patient registered!</p>";
        }
        ?>

        <h3>Patient List</h3>
        <table>
            <tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Phone</th></tr>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM patients");
            while($row = mysqli_fetch_assoc($result)){
                echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['age']}</td><td>{$row['gender']}</td><td>{$row['phone']}</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>