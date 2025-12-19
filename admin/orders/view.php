<?php
include_once("../includes/db_config.php");
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;

// Fetch order info
$order_sql = "SELECT o.*, u.firstname, u.lastname, u.email, u.phone, a.address_line1, a.address_line2, a.city, a.state, a.country, a.zip
              FROM orders o
              JOIN users u ON o.user_id = u.id
              JOIN addresses a ON o.address_id = a.id
              WHERE o.id = $order_id";
$order = $db->query($order_sql)->fetch_assoc();

// Fetch items
$items_sql = "SELECT oi.*, i.variant, p.name 
              FROM order_items oi
              JOIN inventory i ON oi.inventory_id = i.id
              JOIN products p ON i.product_id = p.id
              WHERE oi.order_id = $order_id";
$items_result = $db->query($items_sql);

// Fetch payment
$payment_sql = "SELECT * FROM payments WHERE order_id = $order_id";
$payment = $db->query($payment_sql)->fetch_assoc();

// Fetch shipment
$shipment_sql = "SELECT * FROM shipments WHERE order_id = $order_id";
$shipment = $db->query($shipment_sql)->fetch_assoc();
?>

<h2>Order #<?= $order['id'] ?></h2>
<h3>Customer Info</h3>
<p>Name: <?= $order['firstname'].' '.$order['lastname'] ?><br>
Email: <?= $order['email'] ?><br>
Phone: <?= $order['phone'] ?></p>

<h3>Shipping Address</h3>
<p><?= $order['address_line1'] ?> <?= $order['address_line2'] ?><br>
<?= $order['city'] ?>, <?= $order['state'] ?>, <?= $order['country'] ?> - <?= $order['zip'] ?></p>

<h3>Items Ordered</h3>
<table border="1">
    <tr>
        <th>Product</th>
        <th>Variant</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total</th>
    </tr>
    <?php while($item = $items_result->fetch_assoc()): ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td><?= $item['variant'] ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>$<?= $item['price'] ?></td>
            <td>$<?= $item['total'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<h3>Payment</h3>
<p>Method: <?= $payment['payment_method'] ?><br>
Amount: $<?= $payment['amount'] ?><br>
Status: <?= $payment['status'] ?><br>
Transaction ID: <?= $payment['transaction_id'] ?></p>

<h3>Shipment</h3>
<p>Carrier: <?= $shipment['carrier'] ?><br>
Tracking #: <?= $shipment['tracking_number'] ?><br>
Shipping Fee: $<?= $shipment['shipping_fee'] ?><br>
Status: <?= $shipment['status'] ?><br>
Shipped At: <?= $shipment['shipped_at'] ?><br>
Delivered At: <?= $shipment['delivered_at'] ?></p>