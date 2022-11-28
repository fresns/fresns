# Release Notes

All notable changes to this project will be documented in this file.


## 2.0.0-beta.3 (2022-11-28)

### Added
- Panel: Engine cookie prefix optional
- Panel: Automatically check and fix plugin enable status
- Panel: Panel publish permission configuration, add option for admin only
- Panel: Dashboard upgrade plugin add progress bar
- Panel: Publish configuration plugin upload page add status judgment

### Features
- Subscribe: Remove subscription table restrictions and open all tables

### Fixes
- API: Fix the problem of unused DTO prompt messages
- API: Group list sublevel_public Logic processing
- Panel: Missing Chinese language site_mode_public_register_type_phone
- Panel: Avoid group custom configuration overwritten

### Changed
- API: Modify the text code for group permission detection
- API: Optimize the verification code login without account automatically registered
- Data: Configure key name `account_cookie_status` to `account_cookies_status`
- Data: Configure key name `account_cookie` to `account_cookies`
- Data: Language package identifier `accountPoliciesCookie` modified to `accountPoliciesCookies`
- Panel: Remove `ConfigHelper` usage from the control panel to avoid caching
- Panel: Optimize automatic and physical upgrade functions
- Frame: fresns/plugin-manager to v2.2.0


## 2.0.0-beta.2 (2022-11-23)

### Added
- API: refactoring token logic
- Panel: site settings support the configuration of "automatic registration without account when login with verify code"

### Fixes
- API: content link handling error when site url is not set
- Panel: unable to download apps from the app market
- Panel: error clear cache

### Changed
- Frame: laravel/framework to v9.41.0
- Frame: fresns/plugin-manager to v2.1.1
- Frame: fresns/market-manager to v2.1.0
- Engine: FresnsEngine to v2.0.0-beta.2
- Theme: ThemeFrame to v2.0.0-beta.2


## 2.0.0-beta.1 (2022-11-22)

- 2.x first public beta
