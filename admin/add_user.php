<?php
include "includes/db_config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $db->real_escape_string($_POST['firstname']);
    $lastname  = $db->real_escape_string($_POST['lastname']);
    $email     = $db->real_escape_string($_POST['email']);
    $phone     = $db->real_escape_string($_POST['phone']);
    $role      = $db->real_escape_string($_POST['role']);
    $status    = $db->real_escape_string($_POST['status']);

    $sql = "INSERT INTO users (firstname, lastname, email, phone, role, status, created_at) 
            VALUES ('$firstname', '$lastname', '$email', '$phone', '$role', '$status', NOW())";

    $db->query($sql);
    header("Location: users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add User</title>
    <link href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet">
</head>
<body class="g-sidenav-show bg-gray-100">
<?php include_once("includes/sidebar.php") ?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
<?php include_once("includes/navbar.php") ?>

  <div class="container-fluid py-4">
        <h2>Add New User</h2>
        <form method="POST">
            Firstname:<br>
            <input type="text" name="firstname" required><br><br>

            Lastname:<br>
            <input type="text" name="lastname" required><br><br>

            Email:<br>
            <input type="email" name="email" required><br><br>

            Phone:<br>
            <input type="text" name="phone"><br><br>

            Role:<br>
            <select name="role">
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
            </select><br><br>

            Status:<br>
            <select name="status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select><br><br>

            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
    </div>

<script src="assets/js/core/bootstrap.min.js"></script>
<script src="assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>
