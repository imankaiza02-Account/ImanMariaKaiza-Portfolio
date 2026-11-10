<?php
include 'DonRegConnection.php';
//This checks if both 'email' and 'token' parameters exist in the URL and the 'isset()' function verifies the variables are set and not null
if (isset($_GET['token']) && isset($_GET['email'])){
    //Then the parameters are given variables as seen below
    $token=$_GET['token'];
    $email=$_GET['email'];
    //This prepares the SQL query to find a user with a matching email, verification token, and a 'Pending' approval status, and it will stop after finding its first match
    $check=$object->prepare("SELECT user_id FROM users WHERE email=? AND verificationToken=? AND approvalStatus='Pending' LIMIT 1");
    //This binds the parameters to replace the '?' placeholders, 'ss' means that both parameters are string, and email will replace the first '?' and token will replace the second '?', and the parameter placeholders '?' are added to prevent SQL Injections.
    $check->bind_param("ss", $email, $token);
    //Runs the query with the provided email and token values
    $check->execute();
    //This gets the results from the execution and stores them in '$result' if a matching user was found
    $result=$check->get_result();
    //Basically all the above checks if there is a donor account with a pending approval status
    if ($result->num_rows>0) {
        //This code is used to update the database when a donor account has been verified
        $updateDB=$object->prepare("UPDATE users SET approvalStatus='Approved', verificationToken=NULL WHERE email=? LIMIT 1");
        $updateDB->bind_param("s", $email);
        $updateDB->execute();
        echo "Email verified! Your account is now active.";
    }
    else {
        echo "Invalid or expired verification link!";
    }
}
?>