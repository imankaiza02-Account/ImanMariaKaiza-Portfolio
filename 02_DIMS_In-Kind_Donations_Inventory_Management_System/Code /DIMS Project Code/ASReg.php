<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'DonRegConnection.php';

//Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $role     = trim($_POST['role']);
    $dateJoined = date('Y-m-d');

    // Validate role
    if ($role !== "Staff" && $role !== "Admin") {
        $error = "Please choose a valid role.";
    } else {

        // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Check duplicate email/username
            $check = $object->prepare("
                SELECT user_id FROM users 
                WHERE username = ? OR email = ?
            ");
            $check->bind_param("ss", $username, $email);
            $check->execute();
            $exists = $check->get_result();

            if ($exists->num_rows > 0) {
                $error = "Username or Email already exists!";
            } else {

                //Insert new staff/admin into users table
                $insert = $object->prepare("
                    INSERT INTO users (username, email, password_hash, contactDetails, dateJoined, role, approvalStatus)
                    VALUES (?, ?, ?, ?, ?, ?, 'Pending')
                ");

                $insert->bind_param("ssssss",
                    $username,
                    $email,
                    $hashedPassword,
                    $phone,
                    $dateJoined,
                    $role
                );

                if ($insert->execute()) {
                echo "<script>
                    alert('Registration successful! Your account is pending approval. Please wait for an Administrator to activate your account.');
                    window.location.href = 'LoginPage.php';
                    </script>";
                 exit();
                }
                else {
                    $error = "Database error: " . $object->error;
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
    <title>DIMS | Staff/Admin Registration</title>
    <link rel="stylesheet" href="ASReg.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">

        <div class="form-box login">
            <form id="form" method="post">

                <h2>STAFF / ADMIN REGISTRATION</h2>

                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div style="color: red; text-align:center; margin-bottom: 1rem;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="input-group">
                    <label class="labelgroup">Username</label>
                    <input type="text" name="username" placeholder="Username" id="user-name" required>
                </div>

                <div class="input-group">
                    <label class="labelgroup">Email</label>
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <label class="labelgroup">Password</label>
                    <input type="password" name="password" placeholder="Password"id="login-pass" required>
                </div>

                <div class="input-group">
                    <label class="labelgroup">Phone Number</label>
                    <input type="tel" name="phone" placeholder="Phone Number" id="num" required>
                </div>

                <div class="input-group">
                    <select name="role" id="selectreg" required>
                        <option value="">-- Select Your Role --</option>
                        <option value="Staff">Staff</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>

                <div class="checkdiv">
                    <input type="checkbox" required>
                    <label>
                        I agree with the 
                        <a href="T&C.html" target="_blank" style="text-decoration:none; color:red;">
                            Terms and Conditions
                        </a>
                    </label>
                </div>

                <div class="regbtnbox">
                    <button class="regbtn" type="submit">Register</button>
                </div>

            </form>
        </div>

        <!-- Right Panel -->
        <div class="infobox">
            <div class="infoboxpanel">
                <i class="fas fa-warehouse logo-icon"></i>
                <h1>Welcome to DIMS</h1>
                <p class="statement">Inventory Management System for In-Kind Donations</p>
                <p class="loginp">Already have an account?</p>
                <div class="loginbtnbox">
                    <a href="LoginPage.php" class="loginbtn">Login</a>
                </div>
            </div>
        </div>

    </div>

<script src="DonRegConfirmInput.js"></script>
</body>
</html>