<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>E-VoiceSpartans Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <?php include('includes/header.php'); ?>

  <main class="container-fluid mt-4">
    <?php include('includes/dashboard.php'); ?>
    <?php include('includes/responses.php'); ?>
    <?php include('includes/profile.php'); ?>
    <?php include('includes/settings.php'); ?>
  </main>

  <?php include('includes/footer.php'); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/script.js"></script>
</body>
</html>
