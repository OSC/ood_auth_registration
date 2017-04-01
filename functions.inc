<?php

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

function display_login_form($error = null){
  $form_action = $_SERVER['REQUEST_URI'];
  $redir = get_redir();

  // only display claims that the user would understand
  // default array_filter removes pairs with empty values
  $provider_claims = array_filter(array(
    "Name" => fetch($_SERVER, "OIDC_CLAIM_idp_name"),
    "Login ID" => fetch($_SERVER, "OIDC_CLAIM_eppn"),
    "Login User" => fetch($_SERVER, "OIDC_CLAIM_name"),
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

function delete_my_dn($user, $dn, &$error)
{
  $success = true;

  $user = escapeshellarg($user);
  $dn = escapeshellarg($dn);

  $cmd = "/usr/local/bin/delete-user-dn --user {$user} --dn {$dn} 2>&1";
  exec($cmd, $output, $return_var);

  $error = implode("\n", $output);
  if($return_var != 0){
    $success = false;
    $error = implode("\n", $output);
  }

  return $success;
}

function list_my_dns($user)
{
    $user = escapeshellarg($user);

    $cmd = "/usr/local/bin/list-user-dns --user {$user} 2>&1";
    exec($cmd, $output, $return_var);
    
    return $output;
}

function get_existing_dns($user) {
    $existing_dns = list_my_dns($user);
    $dns_to_user = array();
    if (isset($existing_dns[0]) && is_string($existing_dns[0])) {
        //  User has one or more existing mappings        
        foreach ($existing_dns as $rawdn) {
            $dn_parts = explode(' ', $rawdn);
            //  Strip off quotes from beginning and end of dn part
            $endpos = strlen($dn_parts[0]) - 2;
            $dn = substr($dn_parts[0], 1,$endpos);
            $username = $dn_parts[1];
            $dns_to_user[$dn] = $username;
        }        
    }
    return $dns_to_user;
}

function debug($var)
{
    echo '<pre>';
    if (! is_array($var) && ! is_object($var)) {
	echo htmlentities($var);
    } else {
        print_r($var);
    }
    echo '</pre>' . PHP_EOL;
}

function dump_OIDC()
{
    foreach ($_SERVER as $key => $value) {
        if (0 === strpos($key, 'OIDC')) {
            debug("$key: $value");
        }
    }
}

function print_existing_mappings_form(array $existing_dns = array(), $current_dn = '', $username = '', $password = '')
{
    ?>
    <form action="?manage=1" method="post">
        <legend>Existing Mappings</legend>
    <?php
    foreach ($existing_dns as $dn => $dn_username) {
        $helptext = '';
        $divclass = '';
        if ($dn === $current_dn) {
            $helptext = '<span class="help-block">You are currently logged-in via this mapping (below)</span>';
            $divclass = ' has-warning';
        }
        ?>
        <div class="form-group<?php echo $divclass; ?>">
            <label>
                <?=$helptext?>
                <input type="radio" name="dn" value="<?php echo htmlentities($dn); ?>" />
                <?php echo htmlentities($dn) . ' (' . htmlentities($dn_username) . ')'; ?>
            </label>
        </div>
        <?php
    }
    ?>
    <input type="hidden" name="username" value="<?php echo htmlentities($username); ?>" />
    <input type="hidden" name="password" value="<?php echo htmlentities($password); ?>" />
    <input type="hidden" name="delete" value="1" />                    
    <button type="submit" class="btn btn-danger">Remove</button>
    <a href="/register/" class="btn btn-link">Go back</a>
    </form>
    <?php
}

function print_delete_confirmation_form($dn = '', $username = '', $password = '')
{
    ?>
    <form action="?manage=1" method="post">
        <input type="hidden" name="delete" value="1" />
        <input type="hidden" name="dn" value="<?=htmlentities($dn)?>" />
        <input type="hidden" name="username" value="<?=htmlentities($username)?>" />
        <input type="hidden" name="password" value="<?=htmlentities($password)?>" />
        <button class="btn btn-danger" type="submit" name="confirm" value="1">Confirm</button>
        <a href="/register/" class="btn btn-link">Go Back</a>
    </form>
    <?php
}

function is_valid_dn_mapping($dn, $username)
{
    $existing_user_dns = get_existing_dns($username);
    //  If the key exists, the user has the dn
    return isset($existing_user_dns[$dn]);
}
