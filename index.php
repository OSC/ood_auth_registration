<?php
/**
 * Present login page for OSC credentials so we can update grid map file.
 *
 * TODO: add description
 */

require_once('ldap.php');

/**
 * Fetch a variable from an object without php warnings
 *
 * @param $source The array containing data
 * @param $key The key that will be inspected
 * @param null $default An optional return value if location is empty
 * @return null Return the value of $source[$key] or null if empty
 */
function fetch($source, $key, $default = NULL) {
    return isset($source[$key]) ? $source[$key] : $default;
}

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

// get redirect url, verify it is relative to host
function get_redir(){
  $redir = isset($_GET['redir']) ? $_GET['redir'] : "/";

  // validate
  if($redir && strlen($redir) > 0 && substr($redir, 0, 1) == "/"){
    // do nothing, this is good
  }
  else{
    $redir = "/";
  }

  return $redir;
}

// return preferred username if issuer is in whitelist of issuers that we want
// to use the preferred username for mapping. otherwise return null.
function default_user(){
  $default_user = null;

  $issuer = fetch($_SERVER, "OIDC_CLAIM_iss");
  $issuers_with_default_user = array(
    "https://idp-dev.osc.edu/auth/realms/osc",
    "https://idp-dev.osc.edu/auth/realms/awesim",
    "https://idp-test.osc.edu/auth/realms/osc",
    "https://idp-test.osc.edu/auth/realms/awesim",
    "https://idp.osc.edu/auth/realms/osc",
    "https://idp.osc.edu/auth/realms/awesim",
  );

  if(in_array($issuer, $issuers_with_default_user)){
    $default_user = fetch($_SERVER, "OIDC_CLAIM_preferred_username");
  }

  return $default_user;
}


function display_login_form($error = null){
  $form_action = $_SERVER['REQUEST_URI'];
  $redir = get_redir();
  $default_user = default_user();

  // only display claims that the user would understand
  // default array_filter removes pairs with empty values
  $provider_claims = array_filter(array(
    "Name" => fetch($_SERVER, "OIDC_CLAIM_idp_name"),
    "Issuer" => fetch($_SERVER, "OIDC_CLAIM_iss"),
    "Login ID" => fetch($_SERVER, "OIDC_CLAIM_eppn"),
    "Login User" => fetch($_SERVER, "OIDC_CLAIM_name"),
    "Preferred Username" => fetch($_SERVER, "OIDC_CLAIM_preferred_username"),
    "Login Email" => fetch($_SERVER, "OIDC_CLAIM_email")
  ));

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

  $cmd = "/usr/local/bin/add-user-dn --user {$user} --dn {$dn} 2>&1";
  // If the /usr/local/bin/add-user-dn script can't be run by apache, you can copy
  // it locally and run it with this.
  // $cmd = "./add-user-dn --user {$user} --dn {$dn} 2>&1";
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
