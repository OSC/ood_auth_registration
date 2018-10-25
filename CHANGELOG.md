## Unreleased

## 0.3.0 (2018-10-25)

### Changed

- Updates for new LDAP schema

## 0.2.0 (2017-06-06)

### Added

- Log out link to nav bar
- Display preferred username and issuer in claims displayed to user

### Changed

- For a white list of issuers (OSC keycloak IDPs), we fix the HPC username to map to as being readonly and the value of OIDC_CLAIM_preferred_username

## 0.1.0 (2017-05-26)

### Added

- Add logout link to nav bar

### Changed

- Use new address for OSC ldap server

### Fixed

- Address warnings when fetching claim headers from `$_SERVER` that are not set
- HTML escape claim header values in view
- Correct text references to OpenID Connect

## 0.0.1 (2016-08-05)

### Added:

  - Initial release
