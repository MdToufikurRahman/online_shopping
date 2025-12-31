<?php
include_once("../includes/db_config.php");
session_start();

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    die("Invalid Invoice");
}

$order_id = (int) $_GET['id'];

/* ===== ORDER INFO ===== */
$sql = "
SELECT 
    o.id AS order_id,
    o.total_amount,
    o.order_status,
    o.payment_status,
    o.created_at,
    u.firstname,
    u.lastname,
    u.email,
    u.phone,
    a.address_line1,
    a.city,
    a.state,
    a.zip
FROM orders o
JOIN users u ON o.user_id = u.id
JOIN addresses a ON o.address_id = a.id
WHERE o.id = ?
";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found");
}

/* ===== ORDER ITEMS ===== */
$sql_items = "
SELECT 
    oi.quantity,
    oi.price,
    oi.total,
    p.name
FROM order_items oi
JOIN inventory i ON oi.inventory_id = i.id
JOIN products p ON i.product_id = p.id
WHERE oi.order_id = ?
";

$stmt = $db->prepare($sql_items);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= htmlspecialchars($order['order_id']) ?></title>
    <style>
  
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background: #f4f4f4;
    }

    
    .invoice-box {
        width: 210mm;       
        min-height: 297mm; 
        margin: auto;
        padding: 20mm;
        border: 1px solid #eee;
        background: #fff;
        box-sizing: border-box;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    th {
        background: #f4f4f4;
    }

    h2 {
        margin-bottom: 0;
    }

    .total {
        font-weight: bold;
    }

   
    @media print {
        body {
            background: none;
        }

        .invoice-box {
            border: none;
            width: auto;
            min-height: auto;
            margin: 0;
            padding: 0;
        }

        @page {
            size: A4;
            margin: 10mm;
        }
    }

   
    .btn {
        padding: 10px 15px;
        margin: 10px 5px;
        font-size: 14px;
        cursor: pointer;
        background: #007BFF;
        color: #fff;
        border: none;
        border-radius: 4px;
    }

    .btn:hover {
        background: #0056b3;
    }
</style>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

</head>
<body>


<div style="text-align: center; margin: 20px 0;">
    <button class="btn" onclick="window.print()">Print Invoice</button>
    <button class="btn" id="download-pdf">Download PDF</button>
</div>

<div class="invoice-box" id="invoice">
    <h2>Invoice</h2>

    <p>
        <strong>Invoice ID:</strong> <?= htmlspecialchars($order['order_id']) ?><br>
        <strong>Order Date:</strong> <?= htmlspecialchars($order['created_at']) ?><br>
        <strong>Status:</strong> <?= htmlspecialchars($order['order_status']) ?> / <?= htmlspecialchars($order['payment_status']) ?>
    </p>

    <p>
        <strong>Customer:</strong> <?= htmlspecialchars($order['firstname'] . ' ' . $order['lastname']) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?><br>
        <strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?><br>
        <strong>Address:</strong> <?= htmlspecialchars($order['address_line1'] . ', ' . $order['city'] . ', ' . $order['state'] . ' ' . $order['zip']) ?>
    </p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>$<?= number_format($item['total'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3" class="total">Grand Total</td>
                <td class="total">$<?= number_format($order['total_amount'], 2) ?></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- PDF Download JS -->
<script>
document.getElementById('download-pdf').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4'); // portrait, millimeter, A4

    const invoice = document.getElementById('invoice');

    doc.html(invoice, {
        callback: function (doc) {
            doc.save('invoice_<?= $order['order_id'] ?>.pdf');
        },
        x: 10,
        y: 10,
        html2canvas: { scale: 0.5 }
    });
});
</script>

</body>
</html>
