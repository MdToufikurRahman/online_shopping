<?php
// Project root for consistent pat-hing
define('DIR', '/online_shopping_system/Admin/');

// Helper function to check active page
function isActive($pagePath)
{
  $current = $_SERVER['PHP_SELF'];
  return strpos($current, $pagePath) !== false ? 'active bg-gradient-dark text-white' : 'text-dark';
}
?>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">

  <!-- Sidenav Header -->
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
      aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand px-4 py-3 m-0" href="https://demos.creative-tim.com/material-dashboard/pages/dashboard" target="_blank">
      <img src="<?= DIR ?>assets/img/logo-ct-dark.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
      <span class="ms-1 text-sm text-dark">Creative Tim</span>
    </a>
  </div>

  <hr class="horizontal dark mt-0 mb-2">

  <!-- Sidenav Navigation -->
  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">

      <!-- Dashboard -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('dashboard.php') ?>" href="<?= DIR ?>dashboard.php">
          <i class="material-symbols-rounded opacity-5">dashboard</i>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      <!-- Product List -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('products') ?>" href="<?= DIR ?>products/index.php">
          <i class="material-symbols-rounded opacity-5">table_view</i>
          <span class="nav-link-text ms-1">Product List</span>
        </a>
      </li>

      <!-- Orders -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('orders') ?>" href="<?= DIR ?>orders/index.php">
          <i class="material-symbols-rounded opacity-5">receipt_long</i>
          <span class="nav-link-text ms-1">Order List</span>
        </a>
      </li>

      <!-- Clients / Users -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('users.php') ?>" href="<?= DIR ?>users.php">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Clients</span>
        </a>
      </li>

      <!-- Brand List -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('brands') ?>" href="<?= DIR ?>brands/index.php">
          <i class="material-symbols-rounded opacity-5">business</i>
          <span class="nav-link-text ms-1">Brand List</span>
        </a>
      </li>

      <!-- Category List -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('categories') ?>" href="<?= DIR ?>categories/index.php">
          <i class="material-symbols-rounded opacity-5">category</i>
          <span class="nav-link-text ms-1">Category List</span>
        </a>
      </li>

      <!-- Section Separator -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Account pages</h6>
      </li>

      <!-- Profile -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('profile.php') ?>" href="<?= DIR ?>pages/profile.php">
          <i class="material-symbols-rounded opacity-5">person</i>
          <span class="nav-link-text ms-1">Profile</span>
        </a>
      </li>

      <!-- Sign Out -->
      <li class="nav-item">
        <a class="nav-link <?= isActive('sign_out.php') ?>" href="<?= DIR ?>sign_out.php">
          <i class="material-symbols-rounded opacity-5">login</i>
          <span class="nav-link-text ms-1">Sign out</span>
        </a>
      </li>

    </ul>
  </div>

  <!-- Sidenav Footer -->
  <div class="sidenav-footer position-absolute w-100 bottom-0">
    <div class="mx-3">
      <a class="btn btn-outline-dark mt-4 w-100" href="https://www.creative-tim.com/learning-lab/bootstrap/overview/material-dashboard?ref=sidebarfree" type="button">Documentation</a>
      <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/material-dashboard-pro?ref=sidebarfree" type="button">Upgrade to pro</a>
    </div>
  </div>

</aside>