<?php

/**
 * Performs LDAP queries and determines the validity of a user's login
 *
 * Contains the data structures necessary to communicate with LDAP servers.
 * Contains a function that is used by the login page to verify that a certain
 * username/password combination is allowed to log in to OSC systems based on a
 * number of criteria.
 *
 * @category   authentication
 * @package    openid
 * @author     Shaun Brady <shaun.brady@nimbisservices.com>
 * @author     Shawn Hall <shall@osc.edu>
 * @copyright  2014 Ohio Supercomputer Center
 * @version    SVN: $Id:$
 * @link       https://svn.osc.edu/browse/openid
 */

/**
 * LDAP connection settings
 * @name $ldap
 * @global array $GLOBALS['ldap']
 */
$GLOBALS['ldap'] = array (
    # Connection settings
    'primary'         => 'ldaps://cts06.osc.edu:636',
    'fallback'        => 'ldaps://cts08.osc.edu:636',
    'protocol'        => 3,
    # AD specific
    'isad'            => false, // are we connecting to Active Directory?
    'lookupcn'        => false, // should we extract CN after the search?
    # Binding account
    'binddn'          => '',
    'password'        => '',
    # User account
    'autodn'          => false, // extract DN from search result, ignore 'testdn'
    'testdn'          => 'uid=%s,ou=People,ou=hpc,o=osc',
    # Searching data
    'searchdn'        => 'ou=People,ou=hpc,o=osc',
    'filter'          => 'uid=%s',

    # Friendly names matching to LDAP attribute names
    'username'        => 'uid',
    'email'           => 'mail',
    'name'            => 'gecos',
    # Note that in LDAP these are loginShell and loginDisabled,
    # but the LDAP library must lowercase these somehow
    'shell'           => 'loginshell',
    'disabled'        => 'logindisabled',
);

/**
 * Custom user info based on LDAP attributes
 * @name $id
 * @global array $GLOBALS['id']
 * DO NOT ENABLE OR EDIT ARRAY ENTRIES! Call to find_ldap() bellow will populate them!
 */
$GLOBALS['id'] = array (
#       'username'              => 'shall',
#       'email'                 => 'shall',
#       'name'                  => 'Shawn Hall',
#       'shell'                 => '/bin/bash',
#       'disabled'              => 'FALSE',
);

/**
 * Search for LDAP account by username. Populate $sreg if found
 * string $username
 */
function find_ldap ($username) {
    global $sreg, $ldap, $profile;

        $no = "no";
        $profile['user_found'] = false;

        if ($username != "") {
                $ds = ldap_connect($ldap['primary']) or $ds = ldap_connect($ldap['fallback']);
                if ($ds) {
            ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,$ldap['protocol']);
            if ($ldap['isad'] == true) ldap_set_option($ds,LDAP_OPT_REFERRALS,0);

                        $r = ldap_bind($ds,$ldap['binddn'],$ldap['password']);
                    $sr = ldap_search($ds,$ldap['searchdn'],sprintf($ldap['filter'],$username));
            $info = ldap_get_entries($ds, $sr);

                        if ($info["count"] == 1) {
                                $no = "ok";
                                $profile['user_found'] = true;
                if ($ldap['lookupcn'] == true) $profile['auth_cn'] = $info[0]['cn'][0];
                if ($ldap['autodn'] == true) $ldap['testdn'] = $info['0']['dn'];

                # Populate user information from LDAP - if (array_key_exists('keyname', $ldap))...
                $sreg['nickname'] = $info[0][$ldap['nickname']][0];
                $sreg['email']    = $info[0][$ldap['email']][0];

                                $values = is_array($ldap['fullname']) ? $ldap['fullname'] : array($ldap['fullname']);
                                $fullname = '';
                            foreach ($values as $vname) {
                        $aname = $info[0][$vname][0];
                        if ($aname != '') $fullname = ($fullname == '' ? $aname : $fullname . ' ' . $aname);
                                }
                                $sreg['fullname'] = $fullname;

                $sreg['country']  = $info[0][$ldap['country']][0];

                # Values not obtained from LDAP
                $sreg['language'] = $ldap['def_language'];
                $sreg['postcode'] = $ldap['def_postcode'];
                $sreg['timezone'] = $ldap['def_timezone'];
                        }
                        ldap_close($ds);
                }
        }
        return $no;
}

/**
 *
 * Tests a username/password combo and returns a message.
 *
 * Perform LDAP bind test with provided username and password
 * Reject user if LDAP attribute that indicates disabled account
 * Reject user if account has expired password
 * Return "ok" if successful auth or an error explanation if not
 *
 * @param string $username  the provided username
 * @param string $password  the provided password
 *
 * @return string  "ok" if user is allowed to log in, or a reason for failure if
 *                 the user is denied
 *
 * @access public
 * @static
 */
function test_ldap ($username, $password) {
    global $ldap;
    // Initially assume user can't log in
    $no = "Invalid username or password.";
    // Disallow empty usernames or passwords
    if (($username != "") && ($password != "")) {
        /* Connect to LDAP servers - this failover probably does NOT work based
           on comments at http://php.net/manual/en/function.ldap-connect.php */
        $ds = ldap_connect($ldap['primary']) or $ds = ldap_connect($ldap['fallback']);
        // If there was a successful connection
        if ($ds) {
            // Set the protocol version
            ldap_set_option($ds,LDAP_OPT_PROTOCOL_VERSION,$ldap['protocol']);
            /* Unused, but good practice to implement. If talking to AD server,
               set necessary options */
            if ($ldap['isad'] == true) ldap_set_option($ds,LDAP_OPT_REFERRALS,0);
            // Attempt to bind by automatically finding DN and using the password
            if ($ldap['autodn'] == true) {
                $r = ldap_bind($ds,$ldap['testdn'],$password);
            }
            else { // otherwise bind explicitly using the username and password
                $r = ldap_bind($ds,sprintf($ldap['testdn'],$username),$password);
            }
            if ($r) { // If it was a successful bind, i.e. a valid username/password combo
                // Query the user in LDAP
                $sr = ldap_search($ds,$ldap['searchdn'],sprintf($ldap['filter'],$username));
                // Parse the returned search results
                $info = ldap_get_entries($ds, $sr);
                // If there is exactly one username match
                if ($info["count"] == 1) {
                    // If automatically finding DN, then set the test DN based on search DN
                    if ($ldap['autodn'] == true) $ldap['testdn'] = $info['0']['dn'];
                    /* Populate user information from LDAP - if (array_key_exists('keyname', $ldap))...
                       Only some of this information is necessary, but thought
                       it would be nice to have if needed in the future */
                    $id['username'] = $info[0][$ldap['username']][0];
                    $id['email']    = $info[0][$ldap['email']][0];
                    $id['name']     = $info[0][$ldap['name']][0];
                    $id['shell']    = $info[0][$ldap['shell']][0];
                    /* Get the LDAP attribute that determines if an account is
                       disabled */
                    $id['disabled'] = $info[0][$ldap['disabled']][0];
                    // If the account is disabled via LDAP attribute or shell, deny access
                    if (strtoupper($id['disabled']) == "TRUE" || $id['shell'] == "/access/denied") {
                        // Let message stay as "Invalid username or password"
                    }
                    /* If the account has an expired password, suggest to the
                       user that they change their password at the appropriate
                       location on the web */
                    elseif ($id['shell'] == "/bin/password_expired") {
                        $no = $id['name'] . ", your password has expired. Please contact OSC Help";
                    }
                    // If the user has passed all of these tests, they must be a
                    // valid user that should be allowed access
                    else {
                        $no = "ok";
                    }
                }
            }
        ldap_close($ds); // Close the open LDAP connection
        }
    }
    return $no; // Return "ok" if allowed access, an error message otherwise
}
