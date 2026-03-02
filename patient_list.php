<?php
session_start();
include('db.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff'){ 
    header("Location: login.php"); exit(); 
}

$staff_name = $_SESSION['admin_name'];

// Fetch all appointments (Most recent first)
$all_patients = mysqli_query($conn, "SELECT * FROM appointments ORDER BY appt_date DESC, appt_time DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Records | HMS</title>
    <style>
        :root { --sidebar: #1e1b4b; --accent: #8b5cf6; --bg: #f8fafc; --success: #10b981; --warning: #f59e0b; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; }

        /* Sidebar */
        .sidebar { width: 280px; height: 100vh; background: var(--sidebar); color: white; position: fixed; }
        .user-box { background: rgba(255,255,255,0.05); padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-box small { color: #94a3b8; text-transform: uppercase; font-size: 10px; font-weight: 700; }
        
        .nav-links { padding: 20px; }
        .nav-links a { display: block; color: #cbd5e1; padding: 12px 15px; text-decoration: none; border-radius: 10px; margin-bottom: 5px; }
        .nav-links a:hover { background: var(--accent); color: white; }
        .nav-links a.active { background: var(--accent); color: white; }

        .main { margin-left: 280px; width: calc(100% - 280px); padding: 40px; }
        
        /* Table Design */
        .record-card { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .search-bar { width: 100%; max-width: 400px; padding: 12px 20px; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 25px; outline: none; transition: 0.3s; }
        .search-bar:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f1f5f9; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #1e293b; }

        .patient-name { font-weight: 600; color: #1e1b4b; }
        .doc-name { color: var(--accent); font-weight: 500; }

        /* Status Badges */
        .badge { padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
        .badge-waiting { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .badge-resolved { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="user-box">
            <small>Active User</small>
            <h3><?php echo $staff_name; ?></h3>
        </div>
        <div class="nav-links">
            <a href="staff_home.php">🏠 Dashboard</a>
            <a href="patient_list.php" class="active">📋 Patient Records</a>
            <a href="logout.php" style="margin-top: 50px; color: #fca5a5;">🚪 Logout</a>
        </div>
    </div>

    <div class="main">
        <div style="margin-bottom: 30px;">
            <h1 style="margin:0; font-size: 28px;">Patient Archive</h1>
            <p style="color: #64748b;">A full history of all hospital consultations.</p>
        </div>

        <div class="record-card">
            <input type="text" class="search-bar" id="search" placeholder="🔍 Search by patient name..." onkeyup="filterTable()">
            
            <table id="patientTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th>Time</th> <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($all_patients) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($all_patients)): ?>
                        <tr>
                            <td style="color: #94a3b8;">#<?php echo $row['id']; ?></td>
                            <td class="patient-name"><?php echo $row['patient_name']; ?></td>
                            <td class="doc-name">Dr. <?php echo $row['doctor_name']; ?></td>
                            <td style="max-width: 200px;"><?php echo $row['reason']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($row['appt_date'])); ?></td>
                            <td><b><?php echo date('h:i A', strtotime($row['appt_time'])); ?></b></td> <td>
                                <?php if(isset($row['status']) && $row['status'] == 'Resolved'): ?>
                                    <span class="badge badge-resolved">✓ Resolved</span>
                                <?php else: ?>
                                    <span class="badge badge-waiting">⏳ Waiting</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:#94a3b8;">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterTable() {
            let input = document.getElementById("search").value.toUpperCase();
            let rows = document.getElementById("patientTable").getElementsByTagName("tr");
            for (let i = 1; i < rows.length; i++) {
                let nameCell = rows[i].getElementsByTagName("td")[1];
                if (nameCell) {
                    let textValue = nameCell.textContent || nameCell.innerText;
                    rows[i].style.display = textValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
                }
            }
        }
    </script>
</body>
</html>