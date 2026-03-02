<?php
session_start();
include('db.php');

// 1. Security Check: Only Doctors allowed
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Doctor'){ 
    header("Location: login.php"); 
    exit(); 
}

$doc_name = $_SESSION['admin_name']; 

// --- TASK: HANDLE RESOLVE (The Handshake) ---
// When doctor clicks the button, status changes to 'Resolved'
if(isset($_POST['resolve_id'])){
    $id = mysqli_real_escape_string($conn, $_POST['resolve_id']);
    $update_query = "UPDATE appointments SET status='Resolved' WHERE id='$id'";
    mysqli_query($conn, $update_query);
    // Refresh the page to show they are gone from the queue
    header("Location: doctor_home.php");
    exit();
}

// --- TASK: FETCH DATA ---
// We fetch ALL 'Waiting' patients for this specific doctor
$query = "SELECT * FROM appointments 
          WHERE doctor_name = '$doc_name' 
          AND status = 'Waiting' 
          ORDER BY appt_date ASC, appt_time ASC";

$appointments = mysqli_query($conn, $query);
$appointment_count = ($appointments) ? mysqli_num_rows($appointments) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Portal | HMS</title>
    <style>
        :root { --primary: #3b82f6; --bg: #f1f5f9; --text: #1e293b; --success: #10b981; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); margin: 0; display: flex; }
        
        /* Sidebar Navigation */
        .sidebar { width: 260px; height: 100vh; background: #1e293b; color: white; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { color: var(--primary); margin-bottom: 5px; font-size: 22px; }
        .sidebar .role-tag { font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 30px; display: block; }
        .sidebar a { display: block; color: #94a3b8; padding: 12px; text-decoration: none; border-radius: 10px; margin-bottom: 8px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: var(--primary); color: white; }
        
        .main { margin-left: 260px; width: calc(100% - 260px); padding: 40px; box-sizing: border-box; }
        
        /* Layout Elements */
        .card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 25px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #edf2f7; }
        td { padding: 18px 15px; border-bottom: 1px solid #edf2f7; font-size: 15px; }
        
        /* Action Button */
        .btn-resolve { 
            background: var(--success); color: white; padding: 10px 20px; border: none;
            border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s;
        }
        .btn-resolve:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
        
        .status-badge { background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Medical Portal</h2>
        <span class="role-tag">Practitioner View</span>
        <h3 style="margin: 0 0 30px 0; font-size: 18px;">Dr. <?php echo htmlspecialchars($doc_name); ?></h3>
        
        <a href="doctor_home.php" class="active">📅 Incoming Queue</a>
        <a href="logout.php" style="margin-top: 60px; color: #fca5a5;">🚪 Sign Out</a>
    </div>

    <div class="main">
        <header style="margin-bottom: 40px;">
            <h1 style="margin:0; font-size: 32px;">Clinical Dashboard</h1>
            <p style="color: #64748b;">You have <b><?php echo $appointment_count; ?></b> patients waiting for consultation.</p>
        </header>

        <div class="card">
            <h3>Active Waiting List</h3>
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Reason for Visit</th>
                        <th>Appt. Date</th>
                        <th>Status</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($appointment_count > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($appointments)): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($row['patient_name']); ?></b></td>
                            <td style="color: #64748b;"><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['appt_date'])); ?></td>
                            <td><span class="status-badge">Waiting</span></td>
                            <td style="text-align:right;">
                                <form method="POST">
                                    <input type="hidden" name="resolve_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn-resolve">Mark Resolved</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 60px; color: #94a3b8;">
                                <div style="font-size: 40px; margin-bottom: 15px;">📋</div>
                                <b>Your queue is empty.</b><br>
                                New intakes from the reception will appear here instantly.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>