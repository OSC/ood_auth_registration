<?php
/**
 * User self-management page
 * 
 * This page will enable users to self-manage their own mappings.  Currently,
 * the only intended functionality is to view and delete existing mappings.
 * 
 * @author Basil Mohamed Gohar <bgohar@osc.edu> 
 */

require_once __DIR__ . '/config.php';
?>

<!DOCTYPE html>
    <head>
        <title><?= $config->title ?> | Manage OpenID Connect Mappings</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
              <a class="navbar-brand" href="/oidc?logout=https%3A%2F%2F<?= htmlentities(urlencode($_SERVER['HTTP_HOST'])) ?>">Logout</a>
            </div>
          </div><!-- /.navbar-collapse -->
        </nav>
        <!-- navbar -->

        <!-- content -->
        <div class="container">

        <h1>Manage User Mappings</h1>
        <p class="lead">View and/or delete your existing mappings</p>
        
          <div class="row">
              <?php
                if (! empty($existing_dns)) {
                    if (isset($_REQUEST['delete']) && isset($_REQUEST['confirm'])) {
                        //  The user is requesting to delete a mapping and has confirmed they are sure..
                        debug($_REQUEST);
                        //  Make a last-ditch validation that the request is valid
                        if (is_valid_dn_mapping($_REQUEST['dn'], $username)) {
                            //  Attempt to delete the mapping
                            debug('We made it this far.');
                            debug(delete_my_dn($username, $_REQUEST['dn'], $error));
                            debug($error);
                            exit;
                        } else {
                            ?>
                            <p class="danger">You have made an invalid request.</p>
                            <?php
                        }
                        exit;
                    } elseif (isset($_REQUEST['delete'])) {
                        //  The user is requesting to delete a mapping but has yet to confirm their decision.
                        debug($_REQUEST);
                        ?>
                        <h2>Delete a mapping <small>Preparing to remove <mark><?=htmlentities($_REQUEST['dn'])?></mark></small></h2>                        
                        <?php
                        print_delete_confirmation_form($_REQUEST['dn'], $username, $password);
                        exit;
                    } else {
                        //  Show them the listing of mappings they have.
                    ?>
                    <div class="col-md-8">
                        <?php print_existing_mappings_form($existing_dns, $_SERVER['PHP_AUTH_USER'], $username, $password); ?>
                    </div>
              <?php
                    }
                }
              ?>
            
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