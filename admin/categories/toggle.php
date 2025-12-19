<?php
include_once("../includes/db_config.php");
$id = $_GET['id'];

$db->query("UPDATE categories SET status=IF(status=1,0,1) WHERE id='$id'");
header("Location: index.php");