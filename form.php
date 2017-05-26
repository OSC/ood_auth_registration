<!DOCTYPE html>
<!--[if lt IE 9]><html class="lt-ie9" lang="en"><![endif]-->
<!--[if gt IE 8]><!--><html lang="en"><!--<![endif]-->

<?php include('config.php'); ?>

<head>
  <title><?= $config->title ?> | Register OpenID Connect Provider</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
</head>

<body>

<!-- navigation bar -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <a class="navbar-brand" href="#"><?= $config->title ?></a>
    </div>
    <div class="navbar-header pull-right">
      <a class="navbar-brand" href="/logout">Log Out</a>
    </div>
  </div><!-- /.navbar-collapse -->
</nav>
<!-- navbar -->


<!-- content -->
<div class="container">

  <?php if ($error != null): ?>
  <div class="alert alert-danger" role="alert"><strong>Registration failed:</strong> <?= $error ?></div>
  <?php endif; ?>

  <h3>Login to register this OpenID Connect Provider with your HPC account</h3>
  <p>This is the first time you have tried logging into OnDemand with this external
  identity provider. By providing your HPC account credentials we will associate
  the two accounts so that in the future you may login just with this identity provider.</p>

  <div class="row">
    <div class="col-md-6">

   <form class="form-horizontal" action="<?= htmlspecialchars($form_action) ?>" method="post">
     <!-- for IE8 we need to add <fieldset> tags to make styling the legend tag possible
     https://github.com/ssolomon/bootstrap/commit/650ae3b454ecbafb050a3bc7397cf2b03bdb34cd
      -->
     <!--[if lt IE 9]><fieldset><![endif]-->
       <legend>HPC Login</legend>

       <div class="form-group">
         <label class="col-sm-2 control-label" for="username">Username:</label>
         <div class="col-sm-7">
           <input class="form-control" type="text" id="username" name="username" placeholder="Username">
         </div>
       </div>

       <div class="form-group">
         <label class="col-sm-2 control-label" for="password">Password:</label>
         <div class="col-sm-7">
           <input class="form-control" type="password" id="password" name="password" placeholder="Password">
         </div>
       </div>

       <div class="form-group">
         <div class="col-sm-offset-2 col-sm-6">
           <button type="submit" class="btn btn-default">Log in</button>
         </div>
       </div>
     <!--[if lt IE 9]></fieldset><![endif]-->
   </form>

    </div>
    <div class="col-md-6">
      <!-- FIXME: address Firefox fieldets issue -->
      <fieldset>
        <legend>OpenID Connect Provider</legend>
          <dl class="dl-horizontal">
            <?php foreach($provider_claims as $key => $value){ ?>
              <dt><?= $key ?></dt><dd><?= htmlentities($value)?></dd>
            <?php } ?>
          </dl>
      </fieldset>
    </div>
  </div>
</div>

<?php if($config->change_password_url){ ?>
<div class="footer">
  <div class="container">
    <hr>
    <p>Forgot password? <a href="<?= $config->change_password_url ?>">Go to <?= $config->change_password_url ?></a> and click "Forgot Password" at the bottom of the page.</p>
  </div>
</div>
<?php } ?>

</body>
</html>
