
<?php
include "includes/db_config.php";


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid request — User ID missing.");
}

$id = intval($_GET['id']); 


$sql = "SELECT * FROM users WHERE id = $id LIMIT 1";
$result = $db->query($sql);

if ($result->num_rows == 0) {
    die("User not found.");
}

$user = $result->fetch_object();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="assets/img/favicon.png">
  <title>Edit User</title>

  <!-- Material Dashboard CSS -->
  <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,700,900" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

</head>

<body class="g-sidenav-show bg-gray-100">

<?php include_once("includes/sidebar.php") ?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  
<?php include_once("includes/navbar.php") ?>

<div class="container-fluid py-4">

  <div class="row">
    <div class="col-lg-8 col-md-10 col-sm-12 mx-auto">

      <div class="card">
        <div class="card-header bg-gradient-dark text-white">
          <h5 class="mb-0">Edit User</h5>
        </div>

        <div class="card-body">

          <form action="update_user.php" method="POST">
            
            <input type="hidden" name="id" value="<?php echo $user->id; ?>">

            <!-- First Name -->
            <div class="input-group input-group-outline my-3">
              <label class="form-label"></label>
              <input type="text" class="form-control" name="firstname" 
                     value="<?php echo $user->firstname; ?>" required>
            </div>

            <!-- Last Name -->
            <div class="input-group input-group-outline my-3">
              <label class="form-label"></label>
              <input type="text" class="form-control" name="lastname" 
                     value="<?php echo $user->lastname; ?>" required>
            </div>

            <!-- Email -->
            <div class="input-group input-group-outline my-3">
              <label class="form-label"></label>
              <input type="email" class="form-control" name="email" 
                     value="<?php echo $user->email; ?>" required>
            </div>

            <!-- Phone -->
            <div class="input-group input-group-outline my-3">
              <label class="form-label"></label>
              <input type="text" class="form-control" name="phone" 
                     value="<?php echo $user->phone; ?>">
            </div>

            <!-- Role -->
            <div class="input-group input-group-static my-3">
              <label class="ms-0">Role</label>
              <select class="form-control" name="role">
                <option value="customer" <?php echo ($user->role == 'customer') ? 'selected' : ''; ?>>Customer</option>
                <option value="admin" <?php echo ($user->role == 'admin') ? 'selected' : ''; ?>>Admin</option>
              </select>
            </div>

            <!-- Status -->
            <div class="input-group input-group-static my-3">
              <label class="ms-0">Status</label>
              <select class="form-control" name="status">
                <option value="1" <?php echo ($user->status == 1) ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ($user->status == 0) ? 'selected' : ''; ?>>Inactive</option>
              </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn bg-gradient-dark w-100 my-4 mb-2">
              Update User
            </button>

          </form>

        </div>
      </div>

    </div>
  </div>

</div>

</main>

<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap.min.js"></script>
<script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="assets/js/material-dashboard.min.js?v=3.2.0"></script>

</body>
</html>
