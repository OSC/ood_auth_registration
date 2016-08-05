# ood_auth_registration

OSC OnDemand Open ID Connect CI Logon Registration page

Summary:

1. display a webform to the user, showing their DN and asking for their OSC credentials
2. upon form submission, bind to ldap to confirm they are who they say they are
3. if successful binding, map the HPC username to the DN using add-user-dn script that apache is given privilege to run on behalf of the user
4. show them success.php, which then redirects the user to their final destination

pages:

* index.php - shows the webform and handles form submission
* whoami.php - for testing purposes, it just shows all of the headers prefixed with `OIDC_CLAIM_`.

resources:

* form.php - web form page template
* success.php - success and redirect page template
* ldap.php - contains function to validate user credentials by binding to ldap
* config.php - branding



## Install

See directions here: https://github.com/OSC/Open-OnDemand#authentication-deploy-the-registration-page

## Configuration and Branding

Edit the config.php file to change the branding of the registration and success pages. A global
instance of Config is used in the php that renders these pages.
