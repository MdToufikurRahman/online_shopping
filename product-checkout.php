<?php
session_start();

// --- 1. DATABASE CONNECTION ---
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'cbpos_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// --- 2. INITIALIZE VARIABLES ---
$message = "";
$order_success = false;
$placed_order_id = 0;
$shipping_cost = 3.00; // Flat rate shipping

// Check if cart is empty
if (empty($_SESSION['cart']) && !isset($_POST['place_order'])) {
    // If cart is empty, redirect to shop (unless we just finished an order)
    header("Location: product.php"); 
    exit();
}

// Helper to calculate cart total
function getCartTotal($cart) {
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['qty'];
    }
    return $subtotal;
}

// --- 3. HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['place_order'])) {

    // Sanitizing Input
    $fname = trim($_POST['firstname']);
    $lname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $addr1 = trim($_POST['address_line1']);
    $city  = trim($_POST['city']);
    $state = trim($_POST['state'] ?? '');
    $zip   = trim($_POST['zip'] ?? '');

    // Validation
    if (empty($fname) || empty($lname) || empty($email) || empty($addr1) || empty($phone)) {
        $message = "<div class='alert alert-danger'>Please fill in all required fields marked with *.</div>";
    } else {
        $conn->begin_transaction();
        try {
            // STEP A: USER LOGIC (Guest vs Existing)
            $checkout_user_id = 0;
            
            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows > 0) {
                // User exists -> Get ID
                $row = $res->fetch_assoc();
                $checkout_user_id = $row['id'];
            } else {
                // User does not exist -> Create Account
                $def_pass = 123456; 
                $role = 'customer';
                $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, phone, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())");
                $stmt->bind_param("ssssis", $fname, $lname, $email, $phone, $def_pass, $role);
                $stmt->execute();
                $checkout_user_id = $conn->insert_id;
            }

            // STEP B: ADDRESS LOGIC
            $stmt = $conn->prepare("INSERT INTO addresses (user_id, address_line1, city, state, zip, is_default) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("issss", $checkout_user_id, $addr1, $city, $state, $zip);
            $stmt->execute();
            $address_id = $conn->insert_id;

            // STEP C: ORDER LOGIC
            $subtotal = getCartTotal($_SESSION['cart']);
            $total_amount = $subtotal + $shipping_cost;

            $stmt = $conn->prepare("INSERT INTO orders (user_id, address_id, total_amount, order_status, payment_status, created_at) VALUES (?, ?, ?, 'pending', 'unpaid', NOW())");
            $stmt->bind_param("iid", $checkout_user_id, $address_id, $total_amount);
            $stmt->execute();
            $placed_order_id = $conn->insert_id;

            // STEP D: ORDER ITEMS LOGIC (*** FIX APPLIED HERE ***)
            // We must find the inventory_id for each product_id
            $stmt_inv_lookup = $conn->prepare("SELECT id FROM inventory WHERE product_id = ? LIMIT 1");
            $stmt_insert_item = $conn->prepare("INSERT INTO order_items (order_id, inventory_id, quantity, price, total) VALUES (?, ?, ?, ?, ?)");

            foreach ($_SESSION['cart'] as $item) {
                $product_id_from_cart = $item['id']; // This is the Product ID
                
                // 1. Find Inventory ID
                $stmt_inv_lookup->bind_param("i", $product_id_from_cart);
                $stmt_inv_lookup->execute();
                $res_inv = $stmt_inv_lookup->get_result();

                if ($row_inv = $res_inv->fetch_assoc()) {
                    $valid_inventory_id = $row_inv['id'];

                    // 2. Insert Item
                    $line_total = $item['price'] * $item['qty'];
                    $stmt_insert_item->bind_param("iiidd", $placed_order_id, $valid_inventory_id, $item['qty'], $item['price'], $line_total);
                    $stmt_insert_item->execute();
                } else {
                    // Critical Error: Product exists in cart but NOT in inventory table
                    throw new Exception("Database Error: Product ID " . $product_id_from_cart . " is missing from the 'inventory' table. Admin must add inventory for this product.");
                }
            }

            // STEP E: PAYMENT LOGIC
            $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, 'Cash on Delivery', ?, 'pending')");
            $stmt->bind_param("id", $placed_order_id, $total_amount);
            $stmt->execute();

            // STEP F: CLEANUP
            unset($_SESSION['cart']); // Clear session cart
            $conn->commit();
            $order_success = true;

        } catch (Exception $e) {
            $conn->rollback();
            $message = "<div class='alert alert-danger'><strong>Order Failed:</strong> " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Checkout - Brancy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="./assets/images/favicon.webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins/font-awesome.min.css">
    <link rel="stylesheet" href="./assets/css/style.min.css">
</head>

<body>

    <div class="wrapper">
        <header class="header-area sticky-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-5 col-sm-6 col-lg-3">
                        <div class="header-logo">
                            <a href="index.php"><img class="logo-main" src="assets/images/logo.webp" width="95" height="68" alt="Logo" /></a>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="header-navigation">
                            <ul class="main-nav justify-content-start">
                                <li><a href="index.php">home</a></li>
                                <li><a href="product.php">shop</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <main class="main-content">
            <nav aria-label="breadcrumb" class="breadcrumb-style1">
                <div class="container">
                    <ol class="breadcrumb justify-content-center">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                    </ol>
                </div>
            </nav>
            <section class="section-space">
                <div class="container">
                    
                    <?php if ($order_success): ?>
                        <div class="row justify-content-center">
                            <div class="col-md-8 text-center">
                                <div class="alert alert-success p-5 shadow-sm">
                                    <h2 class="alert-heading text-success">Order Placed Successfully!</h2>
                                    <hr>
                                    <p class="mb-2 lead">Order ID: <strong>#<?= $placed_order_id; ?></strong></p>
                                    <p class="mb-4">Thank you, <strong><?= htmlspecialchars($fname ?? 'Customer'); ?></strong>. Your order has been received.</p>
                                    
                                    <div class="card bg-light mb-4">
                                        <div class="card-body">
                                            <p class="mb-1"><strong>Payment Method:</strong> Cash on Delivery</p>
                                            <p class="mb-0">Please have the exact amount ready upon delivery.</p>
                                        </div>
                                    </div>
                                    
                                    <a href="product.php" class="btn btn-primary">Continue Shopping</a>
                                </div>
                            </div>
                        </div>
                    
                    <?php else: ?>
                        <?= $message; ?>
                        
                        <form action="" method="post">
                            <div class="row">
                                <div class="col-lg-7">
                                    <div class="billing-details-wrap">
                                        <h3 class="title">Billing Details</h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="fname">First Name <span class="required">*</span></label>
                                                    <input id="fname" type="text" name="firstname" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="lname">Last Name <span class="required">*</span></label>
                                                    <input id="lname" type="text" name="lastname" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="email">Email Address <span class="required">*</span></label>
                                            <input id="email" type="email" name="email" class="form-control" required>
                                            <small class="text-muted">We'll create an account for you if you don't have one.</small>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="phone">Phone <span class="required">*</span></label>
                                            <input id="phone" type="text" name="phone" class="form-control" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="address">Street Address <span class="required">*</span></label>
                                            <input id="address" type="text" name="address_line1" class="form-control" placeholder="House number and street name" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group mb-3">
                                                    <label for="city">Town / City <span class="required">*</span></label>
                                                    <input id="city" type="text" name="city" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="state">State</label>
                                                    <input id="state" type="text" name="state" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="zip">Postcode / ZIP</label>
                                                    <input id="zip" type="text" name="zip" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-5">
                                    <div class="order-summary-details">
                                        <h3 class="title">Your Order</h3>
                                        <div class="order-summary-content">
                                            <div class="order-summary-table table-responsive text-center">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Products</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $cart_subtotal = 0;
                                                        if(isset($_SESSION['cart'])): 
                                                            foreach($_SESSION['cart'] as $item): 
                                                                $line_total = $item['price'] * $item['qty'];
                                                                $cart_subtotal += $line_total;
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <?= htmlspecialchars($item['title'] ?? 'Product'); ?> 
                                                                    <strong> × <?= $item['qty'] ?></strong>
                                                                </td>
                                                                <td>$<?= number_format($line_total, 2) ?></td>
                                                            </tr>
                                                        <?php 
                                                            endforeach; 
                                                        endif;
                                                        ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="cart-subtotal">
                                                            <th>Subtotal</th>
                                                            <td>$<?= number_format($cart_subtotal, 2) ?></td>
                                                        </tr>
                                                        <tr class="shipping">
                                                            <th>Shipping</th>
                                                            <td>$<?= number_format($shipping_cost, 2) ?> (Flat Rate)</td>
                                                        </tr>
                                                        <tr class="order-total">
                                                            <th>Total</th>
                                                            <td><strong>$<?= number_format($cart_subtotal + $shipping_cost, 2) ?></strong></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                            
                                            <div class="order-payment-method">
                                                <div class="single-payment">
                                                    <div class="payment-heading">
                                                        <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                                        <label for="cod">Cash on delivery</label>
                                                    </div>
                                                    <div class="payment-content">
                                                        <p>Pay with cash upon delivery.</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="summary-footer-area">
                                                <button type="submit" name="place_order" class="btn-product btn-product-default w-100">Place Order</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
            </main>

        <footer class="footer-area">
            <div class="footer-bottom">
                <div class="container pt-0 pb-0">
                    <div class="footer-bottom-content">
                        <p class="copyright">© 2025 Cosmetic Shop.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="./assets/js/vendor/modernizr-3.11.7.min.js"></script>
    <script src="./assets/js/vendor/jquery-3.6.0.min.js"></script>
    <script src="./assets/js/vendor/jquery-migrate-3.3.2.min.js"></script>
    <script src="./assets/js/vendor/bootstrap.bundle.min.js"></script>
    <script src="./assets/js/plugins/swiper-bundle.min.js"></script>
    <script src="./assets/js/plugins/fancybox.min.js"></script>
    <script src="./assets/js/plugins/jquery.nice-select.min.js"></script>
    <script src="./assets/js/main.js"></script>

</body>
</html>