<!DOCTYPE html>
<!--[if lt IE 9]><html class="lt-ie9" lang="en"><![endif]-->
<!--[if gt IE 8]><!--><html lang="en"><!--<![endif]-->

<?php include('config.php'); ?>

<head>
  <title><?= $config->title ?> | Register OpenID Provider</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="refresh" content="5;url=<?= htmlspecialchars($redir) ?>">

  <link href="/public/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
  <link rel="stylesheet" href="css/bootstrap.min.css">

<style type="text/css">
.navbar-brand {
  color: <?= $config->nav_brand_text_color ?> !important;
}

.navbar.navbar-inverse {
  background-color: <?= $config->nav_background_color ?> !important;
  border-color: <?= $config->nav_background_color ?> !important;
}

body {
  padding-top: 60px;
}
</style>
<body>

<!-- navigation bar -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <a class="navbar-brand" href="/"><?= $config->title ?></a>
    </div>
  </div><!-- /.navbar-collapse -->
</nav>
<!-- navbar -->


<!-- content -->
<div class="container">
  <h3>Successfully registered provider with HPC account</h3>
  <p class="lead">You should be redirected to your destination in 5 seconds. If not, <a href="/">click here to go to the dashboard.</a></p>
</div>

</body>
</html>
