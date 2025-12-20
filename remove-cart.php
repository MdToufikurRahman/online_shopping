<?php
session_start();

/* Check if cart exists */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* Get product ID safely */
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* Remove product from cart */
if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
}

/* Redirect back to previous page */
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $redirect");
exit;
