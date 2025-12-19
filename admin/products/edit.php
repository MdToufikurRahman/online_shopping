<?php
include_once("../includes/db_config.php");
session_start();

if (!isset($_SESSION['email'])) {
  header("Location: index.php");
  exit;
}

/* ------------------ DEFAULT VALUES ------------------ */
$name = $description = '';
$brand_id = $category_id = 0;
$price = 0.00;
$image_path = '';
$edit_mode = false;
$product_id = null;

/* ------------------ EDIT MODE ------------------ */
if (isset($_GET['id'])) {
  $edit_mode = true;
  $product_id = (int) $_GET['id'];

  $stmt = $db->prepare("
    SELECT 
      p.*, 
      pi.image_path
    FROM products p
    LEFT JOIN product_images pi 
      ON pi.product_id = p.id AND pi.is_primary = 1
    WHERE p.id = ?
  ");
  $stmt->bind_param("i", $product_id);
  $stmt->execute();
  $product = $stmt->get_result()->fetch_object();

  if (!$product) {
    header("Location: index.php");
    exit;
  }

  $name        = $product->name;
  $description = $product->description;
  $brand_id    = $product->brand_id;
  $category_id = $product->category_id;
  $price       = $product->price;
  $image_path  = $product->image_path;
}

/* ------------------ FORM SUBMIT ------------------ */
if (isset($_POST['submit'])) {

  $name        = trim($_POST['name']);
  $description = trim($_POST['description']);
  $brand_id    = (int) $_POST['brand_id'];
  $category_id = (int) $_POST['category_id'];
  $price       = (float) $_POST['price'];

  $db->begin_transaction();

  try {

    /* ---------- INSERT / UPDATE PRODUCT ---------- */
    if ($edit_mode) {
      $stmt = $db->prepare("
        UPDATE products 
        SET name = ?, description = ?, brand_id = ?, category_id = ?, price = ?
        WHERE id = ?
      ");
      $stmt->bind_param(
        "ssiidi",
        $name,
        $description,
        $brand_id,
        $category_id,
        $price,
        $product_id
      );
      $stmt->execute();
    } else {
      $stmt = $db->prepare("
        INSERT INTO products (name, description, brand_id, category_id, price, status)
        VALUES (?, ?, ?, ?, ?, 1)
      ");
      $stmt->bind_param(
        "ssi id",
        $name,
        $description,
        $brand_id,
        $category_id,
        $price
      );
      // FIX binding string (no spaces)
      $stmt->bind_param(
        "ssi id",
        $name,
        $description,
        $brand_id,
        $category_id,
        $price
      );
    }

    // Correct INSERT binding (final)
    if (!$edit_mode) {
      $stmt = $db->prepare("
        INSERT INTO products (name, description, brand_id, category_id, price, status)
        VALUES (?, ?, ?, ?, ?, 1)
      ");
      $stmt->bind_param(
        "ssi id",
        $name,
        $description,
        $brand_id,
        $category_id,
        $price
      );
      $stmt->execute();
      $product_id = $stmt->insert_id;
    }

    /* ---------- IMAGE UPLOAD ---------- */
    if (!empty($_FILES['image']['name'])) {

      $allowed = ['jpg', 'jpeg', 'png', 'webp'];
      $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

      if (!in_array($ext, $allowed)) {
        throw new Exception("Invalid image format");
      }

      $upload_dir = "../uploads/products/";
      if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }

      $filename = uniqid("product_", true) . "." . $ext;
      $full_path = $upload_dir . $filename;

      move_uploaded_file($_FILES['image']['tmp_name'], $full_path);
      $db_path = "uploads/products/" . $filename;

      // Remove old primary image
      $db->query("UPDATE product_images SET is_primary = 0 WHERE product_id = $product_id");

      // Insert new primary image
      $stmt = $db->prepare("
        INSERT INTO product_images (product_id, image_path, is_primary)
        VALUES (?, ?, 1)
      ");
      $stmt->bind_param("is", $product_id, $db_path);
      $stmt->execute();
    }

    $db->commit();
    header("Location: index.php");
    exit;

  } catch (Exception $e) {
    $db->rollback();
    die("Error: " . $e->getMessage());
  }
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
    Material Dashboard 3 by Creative Tim
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

  <style>
    /* Darker shadow and proper input borders */
    .custom-form {
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
      /* darker shadow */
      border-radius: 0.5rem;
      padding: 2rem;
      background-color: #fff;
    }

    .custom-form .form-control,
    .custom-form .form-select,
    .custom-form textarea {
      border: 1px solid #ced4da;
      /* ensure visible border */
      border-radius: 0.375rem;
      padding-left: 10px;
    }

    .custom-form .form-control:focus,
    .custom-form .form-select:focus,
    .custom-form textarea:focus {
      border-color: #495057;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Image preview */
    #imgPreview {
      display: block;
      max-width: 200px;
      max-height: 200px;
      margin-top: 10px;
      border: 1px solid #ced4da;
      border-radius: 0.375rem;
    }
  </style>
</head>

<body class="g-sidenav-show  bg-gray-100">
  <?php include_once("../includes/sidebar.php") ?>


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
                <h6 class="text-white text-capitalize ps-3">Authors table</h6>
              </div>
            </div>


            <!-- Table -->
            <div class="card-body px-3 pb-4">
              <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                  <form method="post" enctype="multipart/form-data" class="custom-form">

                    <h5 class="mb-4 text-center fw-bold"><?= $edit_mode ? "Edit Product" : "Add Product" ?></h5>

                    <!-- Name -->
                    <div class="mb-3">
                      <label class="form-label">Name</label>
                      <input type="text" name="name" class="form-control" placeholder="Enter product name"
                        value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                      <label class="form-label">Description</label>
                      <textarea name="description" class="form-control" rows="3" placeholder="Enter product description"><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
                    </div>

                    <!-- Brand -->
                    <div class="mb-3">
                      <label class="form-label">Brand</label>
                      <select name="brand_id" class="form-select" required>
                        <option value="">-- Select Brand --</option>
                        <?php
                        $b = $db->query("SELECT id, name FROM brands WHERE status = 1");
                        while ($r = $b->fetch_object()):
                          $selected = (isset($brand_id) && $brand_id == $r->id) ? 'selected' : '';
                        ?>
                          <option value="<?= $r->id ?>" <?= $selected ?>><?= htmlspecialchars($r->name) ?></option>
                        <?php endwhile; ?>
                      </select>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                      <label class="form-label">Category</label>
                      <select name="category_id" class="form-select" required>
                        <option value="">-- Select Category --</option>
                        <?php
                        $c = $db->query("SELECT id, name FROM categories WHERE status = 1");
                        while ($r = $c->fetch_object()):
                          $selected = (isset($category_id) && $category_id == $r->id) ? 'selected' : '';
                        ?>
                          <option value="<?= $r->id ?>" <?= $selected ?>><?= htmlspecialchars($r->name) ?></option>
                        <?php endwhile; ?>
                      </select>
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                      <label class="form-label">Price</label>
                      <input
                        type="number"
                        name="price"
                        class="form-control"
                        step="0.01"
                        min="0"
                        required
                        value="<?= htmlspecialchars($price) ?>">
                    </div>


                    <!-- Image Upload -->
                    <div class="mb-4">
                      <label class="form-label">Product Image</label>
                      <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">

                      <img id="imgPreview"
                        src="<?= $image_path ? '../' . htmlspecialchars($image_path) : '#' ?>"
                        style="<?= $image_path ? 'display:block;' : 'display:none;' ?> max-width:200px;margin-top:10px;">
                    </div>


                    <!-- Submit -->
                    <div class="d-grid">
                      <button type="submit" name="submit" class="btn btn-primary"><?= $edit_mode ? "Update Product" : "Save Product" ?></button>
                    </div>

                  </form>

                  <script>
                    function previewImage(event) {
                      const preview = document.getElementById('imgPreview');
                      const file = event.target.files[0];

                      if (file) {
                        preview.src = URL.createObjectURL(file);
                        preview.style.display = 'block';
                      }
                    }
                  </script>


                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <?php include_once("../includes/footer.php") ?>
  </main>


  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script>
    // Live image preview
    function previewImage(event) {
      const preview = document.getElementById('imgPreview');
      const file = event.target.files[0];

      if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
      } else {
        preview.src = '#';
        preview.style.display = 'none';
      }
    }
  </script>

  <script>
    var ctx = document.getElementById("chart-bars").getContext("2d");

    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["M", "T", "W", "T", "F", "S", "S"],
        datasets: [{
          label: "Views",
          tension: 0.4,
          borderWidth: 0,
          borderRadius: 4,
          borderSkipped: false,
          backgroundColor: "#43A047",
          data: [50, 45, 22, 28, 50, 60, 76],
          barThickness: 'flex'
        }, ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: '#e5e5e5'
            },
            ticks: {
              suggestedMin: 0,
              suggestedMax: 500,
              beginAtZero: true,
              padding: 10,
              font: {
                size: 14,
                lineHeight: 2
              },
              color: "#737373"
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 14,
                lineHeight: 2
              },
            }
          },
        },
      },
    });


    var ctx2 = document.getElementById("chart-line").getContext("2d");

    new Chart(ctx2, {
      type: "line",
      data: {
        labels: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"],
        datasets: [{
          label: "Sales",
          tension: 0,
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: "#43A047",
          pointBorderColor: "transparent",
          borderColor: "#43A047",
          backgroundColor: "transparent",
          fill: true,
          data: [120, 230, 130, 440, 250, 360, 270, 180, 90, 300, 310, 220],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              title: function(context) {
                const fullMonths = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
                return fullMonths[context[0].dataIndex];
              }
            }
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [4, 4],
              color: '#e5e5e5'
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 12,
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 12,
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");

    new Chart(ctx3, {
      type: "line",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Tasks",
          tension: 0,
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: "#43A047",
          pointBorderColor: "transparent",
          borderColor: "#43A047",
          backgroundColor: "transparent",
          fill: true,
          data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [4, 4],
              color: '#e5e5e5'
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#737373',
              font: {
                size: 14,
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [4, 4]
            },
            ticks: {
              display: true,
              color: '#737373',
              padding: 10,
              font: {
                size: 14,
                lineHeight: 2
              },
            }
          },
        },
      },
    });
  </script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>