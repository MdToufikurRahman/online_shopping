<?php
include_once("../includes/db_config.php");
session_start();

/* Auth check */
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

/* Product ID check */
if (!isset($_GET['pid'])) {
    die("Product ID missing");
}

$pid = (int) $_GET['pid'];

/* Insert inventory */
if (isset($_POST['submit'])) {
    $variant = $_POST['variant'];
    $price   = $_POST['price'];
    $qty     = $_POST['qty'];

    $db->query("INSERT INTO inventory (product_id, variant, price, quantity)
                VALUES ($pid, '$variant', '$price', '$qty')");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Inventory</title>

    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <link href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">

<?php include_once("../includes/sidebar.php") ?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

<?php include_once("../includes/navbar.php") ?>

<div class="container-fluid py-4">

    <!-- Inventory Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Variant</label>
                    <input type="text" name="variant" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Price</label>
                    <input type="text" name="price" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="qty" class="form-control" required>
                </div>

                <div class="col-12 mt-3">
                    <button type="submit" name="submit" class="btn bg-gradient-dark">
                        Add Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th>Variant</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    $r = $db->query("SELECT * FROM inventory WHERE product_id = $pid ORDER BY id DESC");
                    while ($row = $r->fetch_object()):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row->variant) ?></td>
                            <td><?= $row->price ?></td>
                            <td><?= $row->quantity ?></td>
                        </tr>
                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php $db->close(); ?>

</main>

<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>

</body>
</html>
