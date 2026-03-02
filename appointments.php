<?php 
session_start();
include('db.php'); 

// Logic: Update Status to Completed
if(isset($_GET['complete_id'])){
    $id = $_GET['complete_id'];
    mysqli_query($conn, "UPDATE appointments SET status='Completed' WHERE id=$id");
    header("Location: appointments.php?msg=Status Updated");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HMS | Appointments Control</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --success: #27ae60; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        
        /* Sidebar styling to match your other pages */
        .sidebar { width: 260px; height: 100vh; background: var(--primary); color: white; padding: 25px; position: fixed; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .sidebar h2 { font-size: 1.2rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 15px; }
        .sidebar a { display: block; color: #ecf0f1; padding: 12px; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar a:hover { background: var(--accent); }

        .main-content { margin-left: 310px; padding: 40px; width: calc(100% - 310px); }
        
        /* Modern Form Styling */
        .form-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 40px; }
        select, input { padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; width: 100%; max-width: 300px; display: block; }
        button { padding: 12px 25px; background: var(--accent); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        button:hover { background: #2980b9; }

        /* Modern Table Styling */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        th { background: #f8f9fa; padding: 18px; text-align: left; color: #7f8c8d; font-weight: 600; text-transform: uppercase; font-size: 12px; }
        td { padding: 18px; border-bottom: 1px solid #f1f1f1; font-size: 14px; }
        
        /* Status Badges */
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .waiting { background: #fff3cd; color: #856404; }
        .completed { background: #d4edda; color: #155724; }

        .btn-action { text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; margin-right: 5px; }
        .btn-checkin { background: var(--accent); color: white; }
        .btn-print { background: #95a5a6; color: white; }
        
        @media print { .sidebar, .form-card, h1, h3, .btn-action { display: none; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>HMS Admin</h2>
        <a href="index.php">🏠 Dashboard</a>
        <a href="doctors.php">👨‍⚕️ Manage Doctors</a>
        <a href="patients.php">🤒 Manage Patients</a>
        <a href="appointments.php" style="background:var(--accent);">📅 Appointments</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h1>Appointment Management</h1>
        
        <div class="form-card">
            <h3>Schedule New Appointment</h3>
            <form method="POST">
                <select name="patient_id" required>
                    <option value="">-- Select Patient --</option>
                    <?php
                    $patients = mysqli_query($conn, "SELECT id, name FROM patients");
                    while($p = mysqli_fetch_assoc($patients)) {
                        echo "<option value='{$p['id']}'>{$p['name']}</option>";
                    }
                    ?>
                </select>

                <select name="doctor_id" required>
                    <option value="">-- Select Doctor --</option>
                    <?php
                    $doctors = mysqli_query($conn, "SELECT id, name FROM doctors");
                    while($d = mysqli_fetch_assoc($doctors)) {
                        echo "<option value='{$d['id']}'>{$d['name']}</option>";
                    }
                    ?>
                </select>

                <input type="date" name="app_date" required>
                <button type="submit" name="book_app">Confirm Booking</button>
            </form>
        </div>

        <?php
        if(isset($_POST['book_app'])){
            $p_id = $_POST['patient_id'];
            $d_id = $_POST['doctor_id'];
            $date = $_POST['app_date'];
            $sql = "INSERT INTO appointments (doctor_id, patient_id, app_date, status) VALUES ('$d_id', '$p_id', '$date', 'Waiting')";
            mysqli_query($conn, $sql);
            echo "<script>alert('Appointment Booked!'); window.location='appointments.php';</script>";
        }
        ?>

        <h3>Daily Schedule</h3>
        <table>
            <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $query = "SELECT a.id, a.app_date, p.name as p_name, d.name as d_name, a.status 
                      FROM appointments a
                      JOIN patients p ON a.patient_id = p.id 
                      JOIN doctors d ON a.doctor_id = d.id
                      ORDER BY a.app_date DESC";
            $result = mysqli_query($conn, $query);
            while($row = mysqli_fetch_assoc($result)){
                $statusClass = ($row['status'] == 'Completed') ? 'completed' : 'waiting';
                echo "<tr>
                        <td>{$row['app_date']}</td>
                        <td>{$row['p_name']}</td>
                        <td>{$row['d_name']}</td>
                        <td><span class='badge $statusClass'>{$row['status']}</span></td>
                        <td>";
                
                if($row['status'] == 'Waiting') {
                    echo "<a href='appointments.php?complete_id={$row['id']}' class='btn-action btn-checkin'>Mark Done</a>";
                } else {
                    echo "<button onclick='window.print()' class='btn-action btn-print'>🖨️ Print Slip</button>";
                }
                
                echo "</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>