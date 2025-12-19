<?php
include "includes/db_config.php";

$id = $_GET['id'];

$sql = "DELETE FROM users WHERE id = $id";
$db->query($sql);

header("Location: users.php");
$db->close();
exit;