<?php
/**
 * Present login page for OSC credentials so we can update grid map file.
 *
 * TODO: add description
 */

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

require_once(__DIR__ . '/functions.inc');
require_once(__DIR__ . '/ldap.php');

// If username_in is set, then the user is attempting to log in
if (isset($_POST['username'])) {
    /* Input sanitization mostly done on principle.  I don't believe
       SQL type injection is possible with the library or possibly
       even the protocol.  */
    $error = test_ldap(scrub_username($_POST['username']), scrub_password($_POST['password']));
    // $error = test_ldap("efranz", "xx");
    if ($error == "ok") {
      $add_my_dn_error = null;
      if(add_my_dn($_POST['username'], $_SERVER['PHP_AUTH_USER'], $add_my_dn_error)){
        display_success_page();
      }
      else {
        display_login_form("Mapping failed: " . $add_my_dn_error);
      }
    }
    else {
      display_login_form($error);
    }
    list_my_dns($_POST['username']);
}
else {
    display_login_form();
}
