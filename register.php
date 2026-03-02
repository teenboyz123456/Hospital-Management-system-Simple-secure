<?php
session_start();
include('db.php');

$error = "";

if(isset($_POST['register'])){
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password']; 

    // 1. Check if an Admin already exists in the system
    $check_admin = mysqli_query($conn, "SELECT * FROM users WHERE role='Admin'");
    
    if(mysqli_num_rows($check_admin) > 0){
        // 2. Lock the gate: If an Admin exists, nobody else can register.
        $error = "Registration Locked: The Master Administrator has already been registered.";
    } else {
        // 3. FORCE THE ROLE: Even if the form had a role selector, 
        // we hard-code 'Admin' here so the user HAS to be the admin.
        $sql = "INSERT INTO users (fullname, username, password, role) 
                VALUES ('$fullname', '$user', '$pass', 'Admin')";
        
        if(mysqli_query($conn, $sql)){
            header("Location: login.php?msg=Master Admin Created! You can now login.");
            exit();
        } else {
            $error = "Critical Error: Could not initialize database.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HMS | System Initialization</title>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --text-main: #2d3436; --bg-form: #ffffff; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; color: var(--text-main); }

        .slideshow-container { flex: 1.2; position: relative; background-color: #000; }
        .mySlides { position: absolute; width: 100%; height: 100%; opacity: 0; transition: opacity 1s ease-in-out; background-size: cover; background-position: center; }
        .mySlides.active { opacity: 1; }
        .slideshow-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(44, 62, 80, 0.8) 0%, rgba(0,0,0,0.4) 100%); display: flex; flex-direction: column; justify-content: center; padding: 10%; box-sizing: border-box; color: white; z-index: 2; }

        .form-container { flex: 1; background-color: var(--bg-form); display: flex; justify-content: center; align-items: center; padding: 5%; border-left: 1px solid #edf2f7; }
        .reg-card { width: 100%; max-width: 400px; }
        h2 { color: var(--primary); font-weight: 800; font-size: 2rem; margin-bottom: 5px; }
        .subtitle { color: #636e72; font-size: 15px; margin-bottom: 30px; }

        .input-group { margin-bottom: 15px; }
        input { width: 100%; padding: 16px; border: 2px solid #edf2f7; border-radius: 12px; font-size: 15px; background: #f9f9f9; box-sizing: border-box; }
        button { width: 100%; padding: 16px; background: var(--accent); color: white; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 700; margin-top: 15px; }
        
        .error { background: #ffeaa7; color: #d63031; padding: 12px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; text-align: center; border: 1px solid #fab1a0; }
        .link { display: block; text-align: center; margin-top: 25px; color: var(--accent); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

    <div class="slideshow-container">
        <div class="mySlides active" style="background-image: url('doctor image 1.webp');"></div>
        <div class="mySlides" style="background-image: url('nurse image 2.avif');"></div>
        
        <div class="slideshow-overlay">
            <h1>HMS Portal</h1>
            <p>Master Administration Setup. Initializing secure institutional environment.</p>
        </div>
    </div>

    <div class="form-container">
        <div class="reg-card">
            <h2>System Admin Setup</h2>
            <p class="subtitle">Enter details to create the <b>Primary Admin</b> account.</p>
            
            <?php if($error) echo "<div class='error'>$error</div>"; ?>
            
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="fullname" placeholder="Admin Full Name" required>
                </div>
                <div class="input-group">
                    <input type="text" name="username" placeholder="Admin Username" required>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Admin Password" required>
                </div>

                <button type="submit" name="register">INITIALIZE MASTER ADMIN</button>
            </form>
            
            <a href="login.php" class="link">Return to Sign In</a>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.mySlides');
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000);
    </script>
</body>
</html>