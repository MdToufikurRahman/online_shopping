<?php
include_once("../includes/db_config.php");
session_start();

if (!isset($_SESSION['email'])) {
  header("Location: index.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit;
}

$product_id = (int) $_GET['id'];

/* ---------- FETCH IMAGES ---------- */
$stmt = $db->prepare("
  SELECT image_path 
  FROM product_images 
  WHERE product_id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

/* ---------- DELETE PROCESS ---------- */
$db->begin_transaction();

try {

  // Delete image files from server
  while ($img = $result->fetch_object()) {
    $file = "../" . $img->image_path;
    if (file_exists($file)) {
      unlink($file);
    }
  }

  // Delete images from DB
  $stmt = $db->prepare("DELETE FROM product_images WHERE product_id = ?");
  $stmt->bind_param("i", $product_id);
  $stmt->execute();

  // Delete product
  $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
  $stmt->bind_param("i", $product_id);
  $stmt->execute();

  $db->commit();

  header("Location: index.php");
  exit;
} catch (Exception $e) {
  $db->rollback();
  die("Delete failed: " . $e->getMessage());
}
