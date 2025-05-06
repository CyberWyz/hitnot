<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$error = "";
$success = "";

// Process form submission
if (isset($_POST['submit'])) {
    // Validate and sanitize input
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $lastname = mysqli_real_escape_string($con, $_POST['lastname']);
    $reg_number = mysqli_real_escape_string($con, $_POST['reg_number']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } 
    // Check if username already exists
    else {
        $check_username = mysqli_query($con, "SELECT * FROM users WHERE Username = '$username'");
        if (mysqli_num_rows($check_username) > 0) {
            $error = "Username already exists";
        }
        // Check if email already exists
        else {
            $check_email = mysqli_query($con, "SELECT * FROM users WHERE email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email already exists";
            }
            // Check if registration number already exists
            else {
                $check_reg = mysqli_query($con, "SELECT * FROM users WHERE Reg_Number = '$reg_number'");
                if (mysqli_num_rows($check_reg) > 0) {
                    $error = "Registration number already exists";
                }
            }
        }
    }
    
    // Handle file upload
    $photo = "";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if (!in_array(strtolower($filetype), $allowed)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed";
        } else {
            // Generate unique filename
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/' . $new_filename;
            
            // Move the file
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                $photo = $new_filename;
            } else {
                $error = "Failed to upload image";
            }
        }
    }
    
    // If no errors, add the user
    if (empty($error)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_query = mysqli_query($con, "INSERT INTO users (Username, Lastname, Reg_Number, email, Password, photo, status) 
                                          VALUES ('$username', '$lastname', '$reg_number', '$email', '$hashed_password', '$photo', '$status')");
        
        if ($insert_query) {
            // Log the action
            mysqli_query($con, "INSERT INTO admin_logs (admin_id, action, details, ip_address) 
                              VALUES ($admin_id, 'add_user', 'Added user: $username $lastname ($reg_number)', '{$_SERVER['REMOTE_ADDR']}')");
            
            $success = "User added successfully!";
            
            // Clear form data
            $_POST = array();
        } else {
            $error = "Failed to add user: " . mysqli_error($con);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Admin - Add User</title>
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary) 10%, #224abe 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
        }
        
        .sidebar-brand {
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-menu i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .topbar {
            height: 70px;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .topbar h1 {
            font-size: 1.5rem;
            margin: 0;
            color: var(--dark);
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-info span {
            margin-right: 1rem;
            color: var(--dark);
        }
        
        .logout-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #c82333;
        }
        
        .card {
            background-color: white;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e3e6f0;
            background-color: #f8f9fc;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--dark);
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0