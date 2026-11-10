<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIMS|Registration</title>
    <link rel="stylesheet" href="DonReg.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-box login">
            <form id="form" method="post"> 
            <!--The JavaScript is already handling the form submission to DonRegForm.php. Having an action attribute can cause double submissions.-->
                <h2>DONOR REGISTRATION</h2>
                <div class="input-group">
                    <label for="user-name" class="labelgroup">Username</label>
                    <input type="text" name="username" placeholder="Username" id="user-name" required>
                </div>

                <div class="input-group">
                    <label for="email" class="labelgroup">Email</label>
                    <input type="email" name="email" placeholder="Email" id="email" required>
                </div>

                <div class="input-group">
                    <label for="login-pass" class="labelgroup">Password</label>
                    <input type="password" name="password" placeholder="Password" id="login-pass" required>
                </div>

                <div class="input-group">
                    <label for="num" class="labelgroup">Phone Number</label>
                    <input type="tel" name="phoneNumber" placeholder="Phone Number" id="num" required>
                </div>
                
                <div class="checkdiv">
                    <input type="checkbox" class="checkbtn" id="check-btn" required> 
                    <label for="check-btn" class="checklabel">I agree with the <a style="text-decoration: none; color: red;" href="T&C.html" target="_blank">Term and Conditions</a></label>
                </div>

                <div class="regbtnbox"><button class="regbtn" type="submit">Register</button></div>
                
            </form>
        </div>

        <div class="infobox">
            <div class="infoboxpanel">
                <i class="fas fa-warehouse logo-icon"></i>
                <h1>Welcome To DIMS</h1>
                <p class="statement">An Inventory Management System for Our In-Kind Donations</p>
                <p class="loginp" id="pid">Already have an account?</p>
                <div class="loginbtnbox"><a href="LoginPage.php" class="loginbtn">Login</a></div>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
    <script src="DonReg.js"></script>
    <script src="DonRegConfirmInput.js"></script>
</body>
</html>