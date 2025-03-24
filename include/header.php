<?php
// Start the session
session_start();

// Get the current script name (e.g., /index.php)
$current_page = basename($_SERVER['SCRIPT_NAME']);

// Check if the user is logged in
$is_logged_in = isset($_SESSION['username']);

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
  // Redirect to the login page if not logged in
  header('Location: /authentication/login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>ForesTopia</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="/assets/img/favicon.png" rel="icon">
  <link href="/assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="/assets/css/main.css" rel="stylesheet">

</head>

<body class="about-page">

  <header id="header" class="header d-flex align-items-center light-background sticky-top">
    <div class="container-fluid position-relative d-flex align-items-center justify-content-between">

      <a href="../index.php" class="logo d-flex align-items-center me-auto me-xl-0">
        <h1 class="sitename">ForesTopia</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <!-- Homepage -->
          <li class="<?= ($current_page == 'index.php') ? 'active' : '' ?>"><a href="/index.php">Home</a></li>

          <!-- List of Trees -->
          <li class="dropdown <?= in_array($current_page, ['year0_trees.php', 'year30_trees.php']) ? 'active' : '' ?>">
            <a href="/list_of_trees/year0_trees.php"><span>List of Trees</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li class="<?= ($current_page == 'year0_trees.php') ? 'active' : '' ?>"><a href="/list_of_trees/year0_trees.php">Year 0</a></li>
              <li class="<?= ($current_page == 'year30_trees.php') ? 'active' : '' ?>"><a href="/list_of_trees/year30_trees.php">Year 30</a></li>
            </ul>
          </li>

          <!-- Production Data -->
          <li class="dropdown <?= in_array($current_page, ['production.php', 'damage.php']) ? 'active' : '' ?>">
            <a href="/production_data/production.php"><span>Production Data</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li class="<?= ($current_page == 'production.php') ? 'active' : '' ?>"><a href="/production_data/production.php">Production</a></li>
              <li class="<?= ($current_page == 'damage.php') ? 'active' : '' ?>"><a href="/production_data/damage.php">Damage</a></li>
            </ul>
          </li>

          <!-- Cutting Regime -->
          <li class="dropdown <?= in_array($current_page, ['regime45.php', 'regime50.php', 'regime55.php', 'regime60.php']) ? 'active' : '' ?>">
            <a href="/cutting_regime/regime45.php"><span>Cutting Regime</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li class="<?= ($current_page == 'regime45.php') ? 'active' : '' ?>"><a href="/cutting_regime/regime45.php">Regime 45</a></li>
              <li class="<?= ($current_page == 'regime50.php') ? 'active' : '' ?>"><a href="/cutting_regime/regime50.php">Regime 50</a></li>
              <li class="<?= ($current_page == 'regime55.php') ? 'active' : '' ?>"><a href="/cutting_regime/regime55.php">Regime 55</a></li>
              <li class="<?= ($current_page == 'regime60.php') ? 'active' : '' ?>"><a href="/cutting_regime/regime60.php">Regime 60</a></li>
            </ul>
          </li>

          <!-- Data Visualization -->
          <li class="dropdown <?= in_array($current_page, ['plot.php', 'bar_chart.php']) ? 'active' : '' ?>">
            <a href="/data_visualization/plot.php"><span>Data Visualization</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
            <ul>
              <li class="<?= ($current_page == 'plot.php') ? 'active' : '' ?>"><a href="/data_visualization/plot.php">Trees Plot</a></li>
              <li class="<?= ($current_page == 'bar_chart.php') ? 'active' : '' ?>"><a href="/data_visualization/bar_chart.php">Bar Chart</a></li>
            </ul>
          </li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <div class="header-social-links d-flex align-items-center">
        <?php if ($is_logged_in): ?>
          <span class="me-3">Hello, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <?php endif; ?>
        <a href="/authentication/logout.php" class="logout-icon d-flex align-items-center ms-3">
          <i class="bi bi-box-arrow-right" style="font-size: 1.5rem;" title="Log Out"></i>
        </a>
      </div>

    </div>
  </header>
</body>

</html>