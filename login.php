<?php
/**
 * Present login page for OSC credentials so we can update grid map file.
 *
 * TODO: add description
 */

require_once('ldap.php');

/**
 * Ensures provided username is valid
 *
 * @param string $username  the provided username
 *
 * @return string  the username if it is valid. An empty string if not valid
 *
 * @access public
 * @static
 */
function scrub_username($username){
    $pattern = '/^[a-zA-Z0-9_\.\-]{1,24}$/';
    if(preg_match($pattern, $username, $matches, PREG_OFFSET_CAPTURE)){
        return $username;
    }
    else{
        return ''; /* short circuits test_ldap() */
    }
}

/**
 * Ensures provided password is valid
 *
 * @param string $password  the provided password
 *
 * @return string  the password
 *
 * @access public
 * @static
 */
function scrub_password($password){
    /* We're more lax on passwords....
       mostly because they can contain almost anything.  This is what
       should be escaped based on:
       http://www.cse.ohio-state.edu/cgi-bin/rfc/rfc2254.html
    */
    //$patterns = array();
    //$patterns[0] = '/\\\/';
    //$patterns[1] = '/\*/';
    //$patterns[2] = '/\(/';
    //$patterns[3] = '/\)/';
    //$patterns[4] = '/\0/';
    //$replacements = array();
    //$replacements[0] = '\\\5c';
    //$replacements[1] = '\\\2a';
    //$replacements[2] = '\\\28';
    //$replacements[3] = '\\\29';
    //$replacements[4] = '\\\00';
    //ksort($patterns);
    //ksort($replacements);
    //return preg_replace($patterns, $replacements, $password);
    return $password;
}

function get_redir(){
  //TODO: _GET['redir']
  return "/";
}

function display_login_form($error = null){
  $redir = get_redir();
  $form_action = $_SERVER['SCRIPT_NAME'];
  include "form.php";
}

function display_success_page(){
  $redir = get_redir();
  include "success.php";
}

function add_my_dn($user, $dn, &$error)
{
  $success = true;

  $user = escapeshellarg($user);
  $dn = escapeshellarg($dn);

  // FIXME: uncomment once this script is executable by apache user
  // $cmd = "/usr/local/bin/add-user-dn --user {$user} --dn {$dn} 2>&1";
  $cmd = "./add-user-dn --user {$user} --dn {$dn} 2>&1";
  exec($cmd, $output, $return_var);

  $error = implode("\n", $output);
  if($return_var != 0){
    $success = false;
    $error = implode("\n", $output);
  }

  return $success;
}

// If username_in is set, then the user is attempting to log in
if(isset($_POST['username'])){
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
}
else {
    display_login_form();
}
?>
