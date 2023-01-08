# Release Notes

All notable changes to this project will be documented in this file.


## 2.0.0 (2023-01-09)

### Added
- Panel: Support for emptying the file cache only

### Fixes
- API: Notification messages are generated even when commenting on yourself
- API: Posting permission cache is not cleared after changing user profile
- API: The comment count of a post is not reduced when deleting a comment
- API: Auto-registration when captcha login, sending captcha is not processed compatible

### Changed
- API: independent caching of creator information for posts and comments, synchronization of changed information after modifying user profiles
- Helper: Optimized file finding model
- Frame: laravel/framework to v9.46.0


## 2.0.0-beta.8 (2022-12-24)

### Added
- API: Request header `contentFormat` parameter, allowing to get the content in the specified format
- API: Post information can be previewed with multiple comments
- API: Post information can be previewed for multiple users who like the post
- Panel: New comment preview setting for interactive configuration
- Panel: Interactive configuration adds preview settings for liked users
- Panel: Engine Remote API Host automatically handles `/` endings when saving
- Panel: New cache management page

### Fixes
- API: Fix the problem of cache not being cleaned automatically after post editing
- API: The editor did not judge the quantity limit when uploading files
- Panel: Map setting field error

### Changed
- API: optimized cache mechanism
- Data: The default value of the `post_appends->is_allow` field is changed to `1`
- Frame: composer to v2.5.1
- Frame: laravel/framework to v9.45.1
- Frame: fresns/plugin-manager to v2.3.2


## 2.0.0-beta.7 (2022-12-13)

### Added
- API: Add `latestCommentTime` sub-level comment time parameter to comment messages

### Fixes
- API: Post `latest_comment_at` time field error after successful comment posting
- Data: Cookies language tag not changed successfully
- Panel: Site URL failed to be saved

### Changed
- API: App ID ignored during account and user credentials verification
- Data: Reset initial language pack


## 2.0.0-beta.6 (2022-12-12)

### Added
- API: Update the last comment time of a post after posting a comment
- API: Post and comment list interface, add `allDigest` and `following` parameters
- Helper: Get the file type number according to the file name, not case-sensitive

### Fixes
- API: Logout login error
- API: Captcha template ID mismatch issue
- API: Content type filtering case match
- Panel: Error when detecting version is empty

### Changed
- API: Content types are named in plural `/api/v2/global/{type}/content-types`


## 2.0.0-beta.5 (2022-12-08)

### Added
- API: Verify if the format of headers deviceInfo matches
- API: Comment list, not skipped when the post to which it belongs has been deleted

### Fixes
- API: Post and comment detail page content caching error
- API: Hierarchy error when replying to comments
- API: Count is not rolled back when deleting posts and comments

### Changed
- API: Role publishing interval limit unit, modified from `minutes` to `seconds`
- API: split `token` into `aidToken` and `uidToken` in the headers parameter
- Frame: laravel/framework to v9.43.0
- Frame: fresns/plugin-manager to v2.3.0


## 2.0.0-beta.4 (2022-12-01)

### Added
- API: New cache to improve access speed
- Panel: When saving `URL` and `Path`, filter for left and right spaces and ending `/` symbols
- Model: Add `middle` option to the file information to stitch image parameters from the beginning of the file name
- Data: Add `is_enable` field to data table `user_follows`
- Add Font Awesome Free 6.x to the built-in resources of the main program

### Fixes
- Panel: Extension installation fails with a text mismatch

### Changed
- `interactive` Modify to `interaction`
- API: The verification code is moved to the parameter format after the judgment, to avoid the format error that causes the verification code to expire early
- API: The `App Secret` splice of the signature is modified from `&key=` to `&appSecret=`
- Panel:`foreach` when configuration is saved `continue` when model is empty in loop
- Frame: laravel/framework to v9.42.2


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
