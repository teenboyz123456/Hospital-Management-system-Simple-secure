<?php
session_start();
include('db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff'){ 
    header("Location: login.php"); exit(); 
}

$staff_name = $_SESSION['admin_name'];

// --- Handle Quick Intake Submission ---
if(isset($_POST['quick_intake'])){
    $p_name = mysqli_real_escape_string($conn, $_POST['p_name']);
    // FIXED: Saving the raw name from the DB to ensure it matches the Doctor's session name
    $doc    = mysqli_real_escape_string($conn, $_POST['doc_name']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $date   = date('Y-m-d');
    $time   = mysqli_real_escape_string($conn, $_POST['appt_time']);

    $sql = "INSERT INTO appointments (patient_name, doctor_name, appt_date, appt_time, reason, status) 
            VALUES ('$p_name', '$doc', '$date', '$time', '$reason', 'Waiting')";
    
    if(mysqli_query($conn, $sql)){
        echo "<script>alert('Intake Successful! Appointment set for $time'); window.location='staff_home.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch doctors for the dropdown
$doctors_dropdown = mysqli_query($conn, "SELECT fullname FROM users WHERE role='Doctor'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reception | HMS</title>
    <style>
        :root { --sidebar: #1e1b4b; --accent: #8b5cf6; --bg: #f8fafc; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; color: #1e293b; }

        .sidebar { width: 280px; height: 100vh; background: var(--sidebar); color: white; position: fixed; }
        .user-box { background: rgba(255,255,255,0.05); padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-box h3 { margin: 5px 0 0; font-size: 18px; }

        .nav-links { padding: 20px; }
        .nav-links a { display: block; color: #cbd5e1; padding: 12px 15px; text-decoration: none; border-radius: 10px; margin-bottom: 5px; }
        .nav-links a:hover, .nav-links a.active { background: var(--accent); color: white; }

        .main { margin-left: 280px; width: calc(100% - 280px); padding: 60px; }

        .header-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        .action-card { 
            background: white; padding: 45px 30px; border-radius: 24px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); cursor: pointer; 
            transition: 0.3s; display: flex; flex-direction: column; 
            align-items: center; text-align: center; border: 2px solid transparent;
        }
        .action-card:hover { border-color: var(--accent); transform: translateY(-8px); }
        .icon-box { width: 70px; height: 70px; background: #f5f3ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 20px; }

        /* Modal */
        .modal { display: none; position: fixed; inset: 0; background: rgba(30, 27, 75, 0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(8px); }
        .modal-content { background: white; width: 420px; padding: 40px; border-radius: 28px; }
        
        input, select, textarea { width: 100%; padding: 14px; margin: 10px 0; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 15px; box-sizing: border-box; }
        label { font-size: 12px; color: #64748b; font-weight: 600; margin-left: 5px; }
        
        .btn-prime { background: var(--accent); color: white; border: none; padding: 16px; width: 100%; border-radius: 12px; font-weight: 700; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="user-box">
            <small style="color:#94a3b8; text-transform:uppercase; font-size:10px;">Personnel</small>
            <h3><?php echo $staff_name; ?></h3>
        </div>
        <div class="nav-links">
            <a href="staff_home.php" class="active">🏠 Dashboard</a>
            <a href="patient_list.php">📋 Patient Records</a>
            <a href="logout.php" style="margin-top: 40px; color: #fca5a5;">🚪 Logout</a>
        </div>
    </div>

    <div class="main">
        <h1 style="font-size: 32px; margin-bottom: 10px;">Reception Desk</h1>
        <p style="color: #64748b; margin-bottom: 40px;">Manage incoming patients and schedule their consultation time.</p>

        <div class="header-grid">
            <div class="action-card" onclick="openModal()">
                <div class="icon-box">👤</div>
                <h2 style="margin:0">Patient Intake</h2>
                <p style="margin-top:8px; font-size:14px; color:#64748b">Register patient and set consultation time.</p>
            </div>

            <div class="action-card" onclick="window.location='patient_list.php'">
                <div class="icon-box">📁</div>
                <h2 style="margin:0">Patient Records</h2>
                <p style="margin-top:8px; font-size:14px; color:#64748b">View all patients and check their status.</p>
            </div>
        </div>
    </div>

    <div class="modal" id="intakeModal">
        <div class="modal-content">
            <h2 style="margin-top:0">New Intake</h2>
            <form method="POST">
                <input type="text" name="p_name" placeholder="Patient Full Name" required>
                
                <select name="doc_name" required>
                    <option value="">Select Assigned Doctor</option>
                    <?php while($d = mysqli_fetch_assoc($doctors_dropdown)): ?>
                        <option value="<?php echo $d['fullname']; ?>">Dr. <?php echo $d['fullname']; ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Proposed Consultation Time:</label>
                <input type="time" name="appt_time" value="<?php echo date('H:i'); ?>" required>

                <textarea name="reason" placeholder="Symptoms / Reason for visit" rows="3" required></textarea>

                <button type="submit" name="quick_intake" class="btn-prime">Submit to Records</button>
                <button type="button" onclick="closeModal()" style="width:100%; border:none; background:none; margin-top:15px; color:#94a3b8; cursor:pointer;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('intakeModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('intakeModal').style.display = 'none'; }
    </script>

</body>
</html>