<?php
// Start or resume session to handle login state and error messages
session_start(); 

// Include the database connection file (assumed to define $object for mysqli)
include 'DonRegConnection.php'; 

// Check if a login attempt was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    
    // Clear any error from the previous attempt
    unset($_SESSION['login_error']);

    // Input names from the form: loginInput (username/email) and password
    $loginInput = trim($_POST['loginInput'] ?? ''); 
    $password = $_POST['password'] ?? '';
    
    if (empty($loginInput) || empty($password)) {
        $_SESSION['login_error'] = "Please enter both username/email and password.";
    } else {
        // Prepare the SQL query to find user by email or username from the 'users' table
        $query = $object->prepare("SELECT user_id, username, email, password_hash, role, approvalStatus FROM users WHERE email=? OR username=? LIMIT 1");
        
        if ($query === false) {
             // Database connection/prepare error
             $_SESSION['login_error'] = "Database error: Query preparation failed.";
        } else {
            $query->bind_param("ss", $loginInput, $loginInput);
            $query->execute();
            $result = $query->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // 1. Verify password against the hashed password (using 'password_hash')
                if (password_verify($password, $user['password_hash'])) {

                // SECURITY FIX — prevents role mix and session reuse
                session_regenerate_id(true);

                // 2. Check approval status
                if ($user['approvalStatus'] === "Pending") {
                    $_SESSION['login_error'] = "Your account is currently Pending.";
                    } elseif ($user['approvalStatus'] === "Rejected") {
                    $_SESSION['login_error'] = "Your account was Rejected.";
                    } elseif ($user['approvalStatus'] === "Approved") {

                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === "Donor") {
                header("Location: DonorMainPage.php");
                } elseif ($user['role'] === "Staff") {
                header("Location: StaffDash.php");
                } elseif ($user['role'] === "Admin") {
                header("Location: Adminhome.php");
                } else {
                $_SESSION['login_error'] = "Login successful, but role ('{$user['role']}') not recognized. Please contact admin.";
            }

        if (!isset($_SESSION['login_error'])) exit;
    }
} else {
        // Password incorrect
        $_SESSION['login_error'] = "Invalid username/email or password!";
        }
        } else {
        // No account found
        $_SESSION['login_error'] = "Invalid username/email or password!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIMS|Login</title>
    <!-- Assuming LoginPage.css contains the styling you want -->
    <link rel="stylesheet" href="LoginPage.css?v=1.1"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-box login">
            <!-- action="" posts the form back to this same file -->
            <form action="" method="POST"> 
                <h2> DIMS LOGIN</h2>

                <!-- Success Message Display (from Registration/Other pages) -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div style="color: green; text-align: center; margin-bottom: 1rem; padding: 10px; border: 1px solid green; background-color: #e6ffe6; border-radius: 5px;">
                        <?php echo $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); // Clear success message after display ?>
                <?php endif; ?>
                
                <!-- ERROR MESSAGE Display (set by the PHP block above) -->
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div style="color: red; text-align: center; margin-bottom: 1rem; padding: 10px; border: 1px solid red; background-color: #ffeaea; border-radius: 5px;">
                        <?php echo $_SESSION['login_error']; ?>
                    </div>
                    <?php unset($_SESSION['login_error']); // Clear error message after display ?>
                <?php endif; ?>

                <div class="input-group">
                    <label for="login-name" class="labelgroup">Username or Email</label>
                    <!-- Input name is 'loginInput' (matches PHP logic) -->
                    <input type="text" name="loginInput" placeholder="Username or Email" id="login-name" required>
                </div>

                <div class="input-group">
                    <label for="login-pass" class="labelgroup">Password</label>
                    <!-- Input name is 'password' (matches PHP logic) -->
                    <input type="password" name="password" placeholder="Password" id="login-pass" required>
                </div>
                
                <div class="logbtnbox">
                    <!-- name="login" on the button triggers the PHP logic -->
                    <button class="loginbtn" type="submit" name="login">Login</button>
                </div>
            
                <div class="linediv"><span>or</span></div>

                 <div class="btnforreg">
                    <a href="DonReg.php" class="registerbtn">Register as a Donor</a>
                    <a href="ASReg.php" class="registerbtn">Register as a Staff/Admin</a>
                 </div>
            </form>
        </div>

        <div class="infobox">
            <div class="infoboxpanel">
                 <i class="fas fa-warehouse logo-icon"></i>
                <h1>Welcome To DIMS</h1>
                <p>An Inventory Management System for Our In-Kind Donations</p>
            </div>
        </div>

    </div>
</body>
</html>