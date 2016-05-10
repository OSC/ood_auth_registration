# ood_auth_registration

summary:

1. display a webform to the user, showing their DN and asking for their OSC credentials
2. upon form submission, bind ldap to confirm they are who they say they are
3. if successful binding, map the OSC username to the DN using add-user-dn script that apache is given privilege to run on behalf of the user
4. show them success.php, which then redirects the user to their final destination

pages:

* index.php - shows the webform and handles form submission
* whoami.php - for testing purposes, it just shows all of the headers prefixed with `OIDC_CLAIM_`.

resources:

* form.php - web form page template
* success.php - success and redirect page template
* ldap.php - contains function to validate user credentials by binding to ldap



## Install

Currently deployed as symlink:

```
efranz@websvcs08:~/dev/ood_auth_map (master)$ ls
/opt/rh/httpd24/root/var/www/html/register -dl
lrwxrwxrwx 1 root root 31 Apr 29 12:21
/opt/rh/httpd24/root/var/www/html/register -> /nfs/17/efranz/dev/ood_auth_map
```
