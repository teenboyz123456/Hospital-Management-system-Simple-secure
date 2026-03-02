<?php
session_start();
include('db.php');

$error = "";

if(isset($_POST['login'])){
    // Sanitize inputs
    $user = mysqli_real_escape_string($conn, $_POST['user']);
    $pass = $_POST['pass'];
    $selected_role = mysqli_real_escape_string($conn, $_POST['role']); 

    // 1. Check if they clicked a role card (UI requirement)
    if(empty($selected_role)){
        $error = "Please select a department card (Admin, Doctor, or Staff) first!";
    } else {
        // 2. THE LOCK: Check Username, Password, AND that the role matches what's in the DB
        // This ensures a 'Staff' member can't login by clicking the 'Admin' card.
        $query = "SELECT * FROM users WHERE username='$user' AND password='$pass' AND role='$selected_role'";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);
            
            // 3. Setup Sessions for the rest of the site
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['role'] = $row['role']; 
            $_SESSION['username'] = $row['username'];
            
            // This matches the security check in your admin.php
            if($row['role'] == 'Admin'){
                $_SESSION['admin'] = true; 
            }

            // 4. FORCED REDIRECTION
            if($row['role'] == 'Admin'){
                header("Location: admin.php");
            } elseif($row['role'] == 'Doctor'){
                header("Location: doctor_home.php");
            } elseif($row['role'] == 'Staff'){
                header("Location: staff_home.php");
            }
            exit(); 
        } else {
            // 5. ERROR: Either wrong password OR they are trying to access a role they don't have
            $error = "Access Denied: Invalid credentials for $selected_role portal.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HMS | Secure Portal</title>
    <style>
        :root { --primary: #27ae60; --dark: #1e293b; --light: #f4f7f6; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; height: 100vh; display: flex; overflow: hidden; background: var(--light); }
        
        /* Hero Branding Side */
        .hero-side { flex: 1.5; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('nurse 2.avif') center/cover; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; text-align: center; padding: 20px; }
        .hero-side h1 { font-size: 3rem; margin-bottom: 10px; }
        
        /* Role Card Selection */
        .role-container { display: flex; gap: 20px; margin-top: 30px; }
        .role-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 25px; border-radius: 20px; width: 140px; text-align: center; cursor: pointer; border: 2px solid transparent; transition: 0.3s; }
        .role-card i { font-size: 2rem; display: block; margin-bottom: 10px; }
        .role-card:hover { background: rgba(255,255,255,0.2); transform: translateY(-5px); }
        .role-card.active { border-color: var(--primary); background: white; color: var(--dark); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }

        /* Login Form Side */
        .login-side { flex: 1; background: white; display: flex; justify-content: center; align-items: center; padding: 40px; }
        .login-box { width: 100%; max-width: 350px; }
        .login-box h2 { font-size: 24px; margin-bottom: 25px; color: var(--dark); }
        
        input { width: 100%; padding: 15px; margin-bottom: 15px; border: 2px solid #eee; border-radius: 12px; font-size: 16px; transition: 0.3s; }
        input:focus { border-color: var(--primary); outline: none; }
        
        button { width: 100%; padding: 16px; background: var(--primary); color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; }
        button:hover { background: #219150; }
        
        .error { color: #e74c3c; background: #ffdada; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-size: 14px; border: 1px solid #f5b7b1; }
    </style>
</head>
<body>

    <div class="hero-side">
        <h1>Hospital Management</h1>
        <p>Please select your department card to access your workspace</p>
        
        <div class="role-container">
            <div class="role-card" onclick="selectRole('Admin', this)">
                <span>⚙️</span>
                <h3>Admin</h3>
            </div>
            <div class="role-card" onclick="selectRole('Doctor', this)">
                <span>👨‍⚕️</span>
                <h3>Doctor</h3>
            </div>
            <div class="role-card" onclick="selectRole('Staff', this)">
                <span>📋</span>
                <h3>Staff</h3>
            </div>
        </div>
    </div>

    <div class="login-side">
        <div class="login-box">
            <h2>Portal Sign In</h2>
            
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="role" id="selectedRole">
                
                <input type="text" name="user" placeholder="Username" required>
                <input type="password" name="pass" placeholder="Password" required>
                
                <button type="submit" name="login" id="loginBtn">Select a Role Above</button>
            </form>
            
            <p style="text-align: center; color: #94a3b8; font-size: 13px; margin-top: 25px;">
                Authorized Personnel Only. <br> Contact Admin for account registration.
            </p>
        </div>
    </div>

    <script>
        function selectRole(role, element) {
            // Update hidden input
            document.getElementById('selectedRole').value = role;
            
            // Visual feedback
            document.querySelectorAll('.role-card').forEach(card => card.classList.remove('active'));
            element.classList.add('active');
            
            // Update button text
            document.getElementById('loginBtn').innerText = "Log In as " + role;
        }
    </script>
</body>
</html>