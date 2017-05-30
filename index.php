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

if (isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
    //  User has submitted account info in an attempt to log in

    $username = scrub_username($_POST['username']);
    $password = scrub_password($_POST['password']);

    $ldap_test_result = test_ldap(scrub_username($_POST['username']), scrub_password($_POST['password']));

    if ('ok' === $ldap_test_result) {
        if (isset($_REQUEST['manage'])) {
            //  Username, password, and account are valid
            $existing_dns = get_existing_dns($username);
            if ($existing_dns) {
                require_once __DIR__ . '/manage.php';
                exit;
            } else {
                debug('User has no existing mappings.');
            }
        } else {
            // $error = test_ldap("efranz", "xx");
            $add_my_dn_error = null;
            if (add_my_dn($_POST['username'], $_SERVER['PHP_AUTH_USER'], $add_my_dn_error)) {
                display_success_page();
            } else {
                display_login_form("Mapping failed: " . $add_my_dn_error);
            }
        }
    } else {
        display_login_form($ldap_test_result);
    }
} else {
    display_login_form();
}
