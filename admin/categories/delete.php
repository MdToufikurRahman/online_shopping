<?php
include_once("../includes/db_config.php");
$id = $_GET['id'];

$db->query("DELETE FROM categories WHERE id='$id'");
header("Location: index.php");
