<?php

session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "Design_inside");
if($conn->connect_error){
    die("Database connection failed: " . $conn->connect_error);
}

// LOGIN
if(isset($_POST['login'])){
    $user_input = trim($_POST['user_input']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM Admins WHERE (email=? OR username=?) AND password=?");
    $stmt->bind_param("sss", $user_input, $user_input, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user){
        $_SESSION['admin'] = $user['username'];
        header("Location: admin/dashboard.php");
        exit;
    }else{
        echo "<script>alert('❌ Wrong username/email or password'); window.location.href='index.php';</script>";
    }
}


// FORGOT PASSWORD
if(isset($_POST['forgot'])){
    $user_input = trim($_POST['user_input']);
    $security_question = trim($_POST['security_question']);
    $security_answer = trim($_POST['security_answer']);
    $new_password = trim($_POST['new_password']);

    $stmt = $conn->prepare("SELECT * FROM Admins WHERE email=? OR username=?");
    $stmt->bind_param("ss", $user_input, $user_input);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if(!$user){
        echo "<script>alert('❌ User not found'); window.history.back();</script>";
        exit;
    }

    if($user['security_question'] != $security_question || strtolower(trim($user['security_answer'])) != strtolower($security_answer)){
        echo "<script>alert('❌ Security question/answer mismatch'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("UPDATE Admins SET password=? WHERE email=?");
    $stmt->bind_param("ss", $new_password, $user['email']);
    $stmt->execute();

    echo "<script>alert('✅ Password updated. Login now'); window.location.href='index.php';</script>";
}

?>