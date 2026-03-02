<?php
session_start();
include('db.php');

if(!isset($_SESSION['admin'])){ header("Location: login.php"); exit(); }

// --- Handle Deletion of User ---
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    header("Location: admin.php#workers-section");
    exit();
}

// --- Handle Worker Registration ---
if(isset($_POST['save_worker'])){
    $fname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $uname = mysqli_real_escape_string($conn, $_POST['username']);
    $pass  = $_POST['password'];
    $role  = $_POST['role'];

    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username='$uname'");
    if(mysqli_num_rows($checkUser) > 0){
        echo "<script>alert('Error: Username already taken!');</script>";
    } else {
        $query = "INSERT INTO users (fullname, username, password, role) VALUES ('$fname', '$uname', '$pass', '$role')";
        if(mysqli_query($conn, $query)){
            echo "<script>alert('Worker Registered!'); window.location='admin.php';</script>";
        }
    }
}

$name = $_SESSION['admin_name'];

// --- Dashboard Stats ---
$doc_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='Doctor'"))['total'] ?? 0;
$staff_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='Staff'"))['total'] ?? 0;
$history_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM appointments WHERE status='Resolved'"))['total'] ?? 0;

// --- Fetch Workers ---
$filter = $_GET['view'] ?? 'all';
if($filter == 'doctors') {
    $sql = "SELECT * FROM users WHERE role='Doctor' ORDER BY id DESC";
} elseif($filter == 'reception') {
    $sql = "SELECT * FROM users WHERE role='Staff' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM users ORDER BY id DESC";
}
$all_workers = mysqli_query($conn, $sql);

// --- Fetch Patient History (Resolved Cases) ---
$history_sql = "SELECT * FROM appointments WHERE status='Resolved' ORDER BY appt_date DESC, appt_time DESC LIMIT 10";
$patient_history = mysqli_query($conn, $history_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HMS | Admin Dashboard</title>
    <style>
        :root { --primary: #27ae60; --sidebar: #1e293b; --bg: #f8fafc; --text: #0f172a; --danger: #e74c3c; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); display: flex; }

        .sidebar { width: 260px; height: 100vh; background: var(--sidebar); color: white; position: fixed; padding: 30px 20px; }
        .sidebar h2 { color: var(--primary); margin-bottom: 40px; font-size: 22px; }
        .sidebar a { display: block; color: #94a3b8; padding: 14px; text-decoration: none; border-radius: 12px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(39, 174, 96, 0.1); color: var(--primary); }

        .main { margin-left: 260px; width: 100%; }
        .top-nav { height: 80px; background: white; display: flex; align-items: center; justify-content: space-between; padding: 0 40px; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 100; }
        .content { padding: 40px; }

        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); border-top: 4px solid var(--primary); }

        .table-container { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 40px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #64748b; font-size: 11px; text-transform: uppercase; border-bottom: 2px solid #f4f7f6; }
        td { padding: 15px; border-bottom: 1px solid #f4f7f6; font-size: 14px; }

        .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .role-doctor { background: #dcfce7; color: #166534; }
        .role-staff { background: #fef9c3; color: #854d0e; }
        .status-resolved { background: #e0f2fe; color: #0369a1; }
        
        .btn-register { background: var(--primary); color: white; padding: 12px 24px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; }
        .btn-delete { color: var(--danger); text-decoration: none; font-size: 12px; font-weight: 700; border: 1px solid var(--danger); padding: 5px 10px; border-radius: 6px; }

        .section-title { margin: 40px 0 20px; display: flex; align-items: center; gap: 10px; font-size: 20px; }
        
        /* Modal Style */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(5px); }
        .modal-content { background: white; padding: 35px; border-radius: 20px; width: 400px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🏥 HMS Admin</h2>
        <a href="admin.php" class="active">📊 Dashboard</a>
        <a href="logout.php" style="margin-top:150px; color:#fca5a5;">Logout System</a>
    </div>

    <div class="main">
        <nav class="top-nav">
            <span style="font-weight: 600; color: #64748b;">Overview / <?php echo date('F Y'); ?></span>
            <button class="btn-register" onclick="openModal()">+ Register Worker</button>
        </nav>

        <div class="content">
            <div class="grid">
                <div class="card">
                    <small>Total Doctors</small>
                    <h2><?php echo $doc_count; ?></h2>
                </div>
                <div class="card" style="border-color: #f1c40f;">
                    <small>Receptionist Staff</small>
                    <h2><?php echo $staff_count; ?></h2>
                </div>
                <div class="card" style="border-color: #3498db;">
                    <small>Completed Consultations</small>
                    <h2><?php echo $history_count; ?></h2>
                </div>
            </div>

            <h3 class="section-title">👥 Personnel Management</h3>
            <div class="table-container" id="workers-section">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($all_workers)): ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo $row['fullname']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><span class="badge <?php echo ($row['role']=='Doctor')?'role-doctor':'role-staff'; ?>"><?php echo $row['role']; ?></span></td>
                            <td><a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete user?')">Delete</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <h3 class="section-title" id="history-section">📜 Recent Patient History (Archives)</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Assigned Doctor</th>
                            <th>Diagnosis/Reason</th>
                            <th>Date Resolved</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($patient_history) > 0): ?>
                            <?php while($p = mysqli_fetch_assoc($patient_history)): ?>
                            <tr>
                                <td><b><?php echo $p['patient_name']; ?></b></td>
                                <td>Dr. <?php echo $p['doctor_name']; ?></td>
                                <td style="color: #64748b;"><?php echo $p['reason']; ?></td>
                                <td><?php echo date('d M, Y', strtotime($p['appt_date'])); ?></td>
                                <td><span class="badge status-resolved">Completed</span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:30px; color:#94a3b8;">No resolved history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="registerModal">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px;">Register New Staff</h3>
            <form method="POST">
                <input type="text" name="fullname" placeholder="Full Name" style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;" required>
                <input type="text" name="username" placeholder="Username" style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;" required>
                <input type="password" name="password" placeholder="Password" style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;" required>
                <select name="role" style="width:100%; padding:12px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;" required>
                    <option value="Doctor">Doctor</option>
                    <option value="Staff">Staff/Reception</option>
                </select>
                <button type="submit" name="save_worker" class="btn-register" style="width:100%;">Save Worker</button>
                <button type="button" onclick="closeModal()" style="width:100%; border:none; background:none; margin-top:10px; color:#94a3b8; cursor:pointer;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('registerModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('registerModal').style.display = 'none'; }
    </script>
</body>
</html>