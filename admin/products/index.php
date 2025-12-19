<?php
include_once("../includes/db_config.php");
session_start();

if (!isset($_SESSION['email'])) {
  header("../Location: index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>

  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
  <?php include_once("../includes/sidebar.php") ?>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <?php include_once("../includes/navbar.php") ?>

    <!-- Content -->
    <div class="container-fluid py-2">
      <div class="row">
        <div class="col-12">
          <div class="card my-4">

            <!-- Card Header -->
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Product Table</h6>
              </div>
            </div>

            <!-- Products Table -->
            <div class="card-body px-4 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Image</th>
                      <th>Name</th>
                      <th>Brand</th>
                      <th>Category</th>
                      <th>Price</th>
                      <th>Status</th>
                      <th>Quantity In Stock</th>
                      <th colspan="2">Action</th>
                    </tr>

                  </thead>
                  <tbody>

                    <?php
                    $sql = "
                      SELECT 
                        p.*,
                        b.name AS brand,
                        c.name AS category,
                        pi.image_path
                      FROM products p
                      JOIN brands b ON p.brand_id = b.id
                      JOIN categories c ON p.category_id = c.id
                      LEFT JOIN product_images pi 
                        ON pi.product_id = p.id AND pi.is_primary = 1
                    ";

                    $res = $db->query($sql);
                    $cnt = 1;

                    while ($row = $res->fetch_object()):
                    ?>
                      <tr>
                        <td><?= $cnt++ ?></td>

                        <!-- Image -->
                        <td>
                          <?php if (!empty($row->image_path)): ?>
                            <img
                              src="../<?= htmlspecialchars($row->image_path) ?>"
                              alt="<?= htmlspecialchars($row->name) ?>"
                              style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                          <?php else: ?>
                            <span class="text-muted">No Image</span>
                          <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($row->name) ?></td>
                        <td><?= htmlspecialchars($row->brand) ?></td>
                        <td><?= htmlspecialchars($row->category) ?></td>
                        <td>
                          <?= number_format((float)$row->price, 2) ?>
                        </td>

                        <!-- Status -->
                        <td>
                          <?= $row->status == 1
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>' ?>
                        </td>

                        <!-- Quantity -->
                        <td>
                          <?= number_format((int)($row->quantity_in_stock ?? 0)) ?>
                        </td>

                        <!-- Actions -->
                        <td>
                          <a href="edit.php?id=<?= $row->id ?>" class="btn btn-sm btn-primary">Edit</a>

                          <a href="delete.php?id=<?= $row->id ?>"
                            onclick="return confirm('Are you sure?')"
                            class="btn btn-sm btn-danger">Delete</a>
                        </td>
                      </tr>
                    <?php endwhile; ?>

                    <?php if ($res->num_rows === 0): ?>
                      <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                          No products found
                        </td>
                      </tr>
                    <?php endif; ?>

                  </tbody>
                </table>
              </div>
            </div>



            <!-- Add Product -->
            <div class="d-flex justify-content-end px-4 pt-2">
              <a href="entry.php" class="btn btn-primary">
                Add Product
              </a>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- Footer -->
    <?= include_once("../includes/footer.php") ?>
  </main>


  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>

</body>

</html>