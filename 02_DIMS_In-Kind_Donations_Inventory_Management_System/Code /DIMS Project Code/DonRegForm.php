<?php
include 'DonRegConnection.php';
if ($object->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["success"=>false, "message"=>"Database connection failed"]);
    exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $username=$_POST['username'];
    $email=$_POST['email'];
    $password=$_POST['password'];
    $phoneNumber=$_POST['phoneNumber'];
    $hashed_password=password_hash($password, PASSWORD_DEFAULT);
    $userType='Donor';
    $approvalStatus='Pending';
    $dateJoined= date('Y-m-d');
    $verificationToken=bin2hex(random_bytes(32));

    //This prepares the SQL statement to check if an email exists in the users table
    //The parameter placeholder '?' prevents SQL injection, which is a vulnerability that allows attackers to interfere with queries of an application and gain access to the database
    //'LIMIT 1' stops searching after finding one match
    $checkEmail=$object->prepare("SELECT user_id FROM users WHERE email=? LIMIT 1");
    //This binds the parameter to the above statement, defining the parameter as string and including '$email' as the variable to check
    $checkEmail->bind_param("s", $email);
    //This executes the statement with the bound parameters and runs the query against the database
    $checkEmail->execute();
    //Stores the results in '$result' variable
    $result=$checkEmail->get_result();
    if ($result->num_rows>0){
        //
        header('Content-Type: application/json');
        echo json_encode(["success"=>false, "message"=>"Email Already Exists"]);
        exit;
    }
    else {
        //This safely inserts a new user into the database using parameter binding to prevent SQL injection
        //This prepares the 'INSERT' statement to add a new user to the database, using the parameter placeholder '?' to prevent SQL injection, and the placeholders are replaced by the actual database value in order below
        $insertValues=$object->prepare("INSERT INTO users (username, email, password_hash, contactDetails, dateJoined, role, approvalStatus, verificationToken) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        //This will access the database and input user data according to the arrangement
        //The 'ssssssss' indicates all 8 parameters are strings, as they appear in php
        $insertValues->bind_param(
                "ssssssss",
                $username,
                $email,
                $hashed_password,
                $phoneNumber,
                $dateJoined,
                $userType,
                $approvalStatus,
                $verificationToken);

        if ($insertValues->execute()){
            // Sending Email Verification
            //This sets up the HTTP response header to indicate this data is JSON, and tells the receiving device to expect JSON format
            //This converts the php array in JSON string and outputs it.
            header('Content-Type: application/json');
            echo json_encode([
           "success"=>true,
           "token"=>$verificationToken,
           "email"=>$email,
           "username"=>$username]);
           //This stops the script execution immediately and will prevent any additional output from being sent, and ensures only the JSON response is returned
            exit;
        }
        else {
            header('Content-Type: application/json');
            echo json_encode(["success"=>false, "message"=>"Database error: ".$object->error]);
            exit;
        }
    }
}
?>