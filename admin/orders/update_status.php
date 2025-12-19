<?php
include_once("../includes/db_config.php");
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'];
    $carrier = $_POST['carrier'];
    $tracking_number = $_POST['tracking_number'];
    $shipping_fee = $_POST['shipping_fee'];
    $shipment_status = $_POST['shipment_status'];

    // Update order
    $db->query("UPDATE orders SET order_status='$order_status', payment_status='$payment_status' WHERE id=$order_id");

    // Update shipment
    $db->query("UPDATE shipments SET carrier='$carrier', tracking_number='$tracking_number', shipping_fee='$shipping_fee', status='$shipment_status' WHERE order_id=$order_id");

    header("Location: view.php?id=$order_id");
    exit();
}

// Fetch current data
$order = $db->query("SELECT order_status, payment_status FROM orders WHERE id=$order_id")->fetch_assoc();
$shipment = $db->query("SELECT * FROM shipments WHERE order_id=$order_id")->fetch_assoc();
?>

<h2>Update Order #<?= $order_id ?></h2>
<form method="post">
    <label>Order Status</label>
    <select name="order_status">
        <?php foreach(['pending','shipped','delivered','cancelled'] as $status): ?>
            <option value="<?= $status ?>" <?= $order['order_status']==$status?'selected':'' ?>><?= ucfirst($status) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Payment Status</label>
    <select name="payment_status">
        <?php foreach(['unpaid','paid'] as $status): ?>
            <option value="<?= $status ?>" <?= $order['payment_status']==$status?'selected':'' ?>><?= ucfirst($status) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <h3>Shipment</h3>
    <label>Carrier</label>
    <input type="text" name="carrier" value="<?= $shipment['carrier'] ?>"><br>
    <label>Tracking Number</label>
    <input type="text" name="tracking_number" value="<?= $shipment['tracking_number'] ?>"><br>
    <label>Shipping Fee</label>
    <input type="text" name="shipping_fee" value="<?= $shipment['shipping_fee'] ?>"><br>
    <label>Status</label>
    <select name="shipment_status">
        <?php foreach(['processing','shipped','delivered','cancelled'] as $status): ?>
            <option value="<?= $status ?>" <?= $shipment['status']==$status?'selected':'' ?>><?= ucfirst($status) ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <input type="submit" value="Update">
</form>