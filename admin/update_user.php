<?php
include "includes/db_config.php";

$id = $_POST['id'];

$firstname = $db->real_escape_string($_POST['firstname']);
$lastname  = $db->real_escape_string($_POST['lastname']);
$email     = $db->real_escape_string($_POST['email']);
$phone     = $db->real_escape_string($_POST['phone']);
$role      = $db->real_escape_string($_POST['role']);
$status    = $db->real_escape_string($_POST['status']);

$sql = "UPDATE users SET 
            firstname='$firstname',
            lastname='$lastname',
            email='$email',
            phone='$phone',
            role='$role',
            status='$status',
            updated_at=NOW()
        WHERE id = $id";

$db->query($sql);

header("Location: users.php");
$db->close();
exit;