# Release Notes

All notable changes to this project will be documented in this file.


## 2.13.2 (2023-05-23)

### Fixes
- Utilities: Queue issues for active user subscriptions


## 2.13.1 (2023-05-23)

### Fixes
- Utilities: Fixed a cache issue with getting characters


## 2.13.0 (2023-05-23)

### Added
- API: Add `viewCount` parameter to data structure for users, groups, hashtags, posts and comments
- API: Five types of content list pages support `view` filter and sort parameters
- Words: New type of subscription command word `4` View event notifications

### Fixes
- Utilities: Publish trigger notifications that fail
- Words: Missing `connectId` when checking account connection credentials
- Words: User extended credit ID `extcreditsId` error

### BREAKING CHANGES
- Words: `subTableName` parameter changed to `subject` for subscription command word
- Words: userExtendedCredits command word `extcredits` parameter changed to `extcreditsId`.


## 2.12.0 (2023-05-18)

### Added
- API: Plugin services for post and comment data can be configured separately
- API: Message notifications add `quote` type
- API: Message notifications support mentioner interaction notifications (someone else's content mentions me, and then notifies me of the interaction action)
- API: Full comment list no longer shows comments visible only to author
- API: Users, groups, topics, posts, comments, lists support random sorting `orderType=random`.
- Words: Add command word `checkHeaders`.
- Words: Add command word `setAccountConnect`
- Words: Add command word `disconnectAccountConnect`

### Fixes
- Panel: Unable to uninstall theme
- API: Map latitude and longitude are reversed
- API: Search command word error
- API: Content detail page command word missing fsid parameter
- Utilities: `in_array` error if private mode whitelist array is empty

### BREAKING CHANGES
- API: Message list interface `actionCid` changed to `contentFsid`.
- API: Change interactive list type `type` parameter value `likers`,`dislikers`,`followers`,`blockers`.
    - /api/v2/user/`{uidOrUsername}`/interaction/`{type}`
    - /api/v2/group/`{gid}`/interaction/`{type}`
    - /api/v2/hashtag/`{hid}`/interaction/`{type}`
    - /api/v2/post/`{pid}`/interaction/`{type}`
    - /api/v2/comment/`{cid}`/interaction/`{type}`
- API: Upload File Interface, if the upload is a user image, the user's profile (avatar image or banner image) is automatically updated without the need to prompt the user to change the profile interface.


## 2.11.2 (2023-05-08)

### Fixes
- API: Post and Comment data plugin provides


## 2.11.1 (2023-05-08)

### Fixes
- API: Percentage of post content preview not calculated correctly
- Data: Incorrect language pack key name
- Data: Inconsistent upgrade dates


## 2.11.0 (2023-05-06)

### Added
- API: Editor uploads files to determine if content type is enabled for upload
- API: Wallet logging interface adds `user` parameter
- API: Added user extcredits logs interface `/api/v2/user/extcredits-logs`.
- Words: Added `setUserExtcredits` command word
- Data: Added `user_extcredits_logs` data table
- Subscribe: Added support for data change `updated` type `SubscribeUtility::CHANGE_TYPE_UPDATED`.
- Panel: Add 'User Extended Score' setting feature
- Panel: Support for creating read-only keys
- Console: Support for installing Fresns in the terminal

### Fixes
- API: Non-public comments do not hide attachment content
- Panel: Migration data conflict for upgrade feature
- Panel: Role permissions configuration view issue

### BREAKING CHANGES
- API: Post and comment data structure `creator` parameter changed to `author`
- Framework: Plugin Manager changed `unikey` to `fskey`
- Framework: Plugin data `pluginUnikey` changed to `pluginFskey`
- Framework: Command word manager `unikeyName` changed to `fsKeyName`


## 2.10.2 (2023-04-27)

### Fixes
- Words: `verifyUrlAuthorization` verify account or user login
- Words: `ipInfo` redundant DTO configuration


## 2.10.1 (2023-04-27)

### Fixes
- API: Post authorization parameter error
- Words: draft pending review cannot be published


## 2.10.0 (2023-04-27)

### Added
- API: Private mode support for whitelist role configuration
- API: Notification message and private message session interfaces add humanised time parameter `timeAgo`.
- API: Add `type` parameter to role list interface
- Words: `verifyAccount` command word support connect token
- Words: Add command word `walletCheckPassword`
- Words: Add command word `addContentMoreInfo`
- Words: Add command word `setContentSticky`
- Words: Add command word `setContentDigest`
- Words: Add command word `setContentCloseDelete`
- Words: Add command word `setPostAuth`
- Words: Add command word `setPostAffiliateUser`
- Words: Add command word `setCommentExtendButton`
- Words: Add command word `setUserExpiryDatetime`
- Words: Add command word `setUserGroupExpiryDatetime`

### Fixes
- API: Incompatible in private mode valid for null
- API: IP interface cannot be requested in private mode
- API: Account and user profile editing, data operation not performed on null request
- API: `replyToComment` data error for comment
- Words: Wrong `originAid` and `originUid` parameters for wallet transaction command word
- Help: Error if configuration file is empty

### BREAKING CHANGES
- API: `/api/v2/common/file/{fid}/users`
    - `downloadTime` changed to `datetime`
    - `downloadTimeFormat` changed to `timeAgo`
    - `downloadUser` changed to `user`
- API: Post and comment data structure change edit control key name
    - `editStatus` changed to `editControls`
- API: Change draft detail parameters in the editor
    - Changed the name of the parameter to determine if a draft is being edited from `edit` to `editControls`
    - Changed the name of the parameter to determine if the draft is being edited from `isEdit` to `isEditDraft`


## 2.9.0 (2023-04-22)

### Added
- Panel: Configurable maximum number of posts per day for role permissions

### Fixes
- API: Upload file interface does not determine file type when uploading private messages
- API: Comment message `replyToComment` parameter formatted incorrectly
- API: User tag interface does not determine whether the group is allowed to follow
- Words: Fix bug with connection information when adding account command word
- Words: Fixed empty draft detection bug when posting comments
- Words: Wallet command word not registered
- Model: fixed bug with missing attachment table for posts and comments

### BREAKING CHANGES
- API: Post and comment data structure changed by creating and modifying key names
    - `createTime` changed to `createdDatetime`
    - `createTimeFormat` changed to `createdTimeAgo`
    - `editTime` changed to `editedDatetime`
    - `editTimeFormat` changed to `editedTimeAgo`
    - `editCount` changed to `editedCount`
    - `latestCommentTime` changed to `latestCommentDatetime`
    - `latestCommentTimeFormat` changed to `latestCommentTimeAgo`
- Install: Changed install function from AlpineJS solution to jQuery
- Utilities: Drop numeric version numbers and use semantic version numbers only
- Marketplace: There are interface customizations in the Application Marketplace, if you do not customize before upgrading, you will not be able to use the Application Marketplace.


## 2.8.1 (2023-04-15)

### Fixes
- Fix data table field errors

### BREAKING CHANGES
- Data: Optimise data indexing
- Panel: Remove cookie.js file


## 2.8.0 (2023-04-14)

### Added
- Data: Support for five databases `MySQL`, `MariaDB`, `PostgreSQL`, `SQL Server`, `SQLite`.
- API: Added `isMultiLevelQuote` and `quotedPost` parameters to the post data structure.
- API: Added `postQuotePid` parameter to editor interface
- API: added `/api/v2/common/ip-info' interface
- API: added `/api/v2/post/{pid}/quotes` interface
- Panel: User nicknames can be configured to be unique or not
- Panel: Topics can be configured with a length limit
- Panel: Topic parsing can be customised with regular expressions
- Panel: Panel non-public mode supports role whitelisting

### Fixes
- API: Interactive manipulation of fsid format to hide incompatible topics
- API: Special symbols for password formatting do not match correctly
- API: Missing language identifier and content for real name authentication
- API: Role posting is checked but not recognised
- Help: MaskName is judged to be empty with a key name error
- Utility: Topic limit 20 characters, avoid very long topics
- Models: Posts and comments do not declare map json fields

### BREAKING CHANGES
- API: Quick post interface, changed `file` parameter to `image
- API: Comment data structure `post` key name changed to `replyToPost`.
- API: Comment data structure `pid` parameter removed
- API: removed `fileCount` parameter from post and comment datastructure
- API: moved post and comment data structure from `ipLocation` parameter to `moreJson` parameter
- API: added group information structure `canViewContent` parameter


## 2.7.2 (2023-03-05)

### Fixes
- API: Fixed issue where the cache count for interactive operations was not cleared.
- API: Fixed miss-configuration in private mode middleware.
- Helper: Fixed issue causing exceptions when domain suffixes were not de-duplicated.
- Words: Updated command word field for account cancellation.

### Changed
- API: Added three new private mode messages to language pack.
- API: Removed pagination feature from `/api/v2/global/configs` configuration interface.
- Framework: fresns/plugin-manager to v2.4.6


## 2.7.1 (2023-03-02)

### Fixes
- Panel: Status code cannot specify `0` parameter
- Panel: Form component input url type can't save when filling path
- Panel: Upgrade status is not synchronized with frontend and backend
- API: After deleting the post of the comment, the error is reported when accessing the comment
- API: Parameter filtering of sub-level preview of comments is cached
- API: The content cache time processing problem after the anti-theft chain is opened

### Changed
- Panel: "Physical Upgrade" renamed to "Manual Upgrade"
- Panel: Email and mobile number login support managed by main application
- API: Editor custom parameter support
- Data: Default home page changed to `post
- Install: PHP extension `exif` requirement removed


## 2.7.0 (2023-02-26)

### Added
- API: List interface adds a period parameter for the creation date, supporting today, yesterday, this week, last week, this month, last month, this year, last year
- API: Notification message list can filter `actionUser` and `actionInfo` key-value pairs
- API: Session message list can be filtered by `user` key/value pairs
- API: User, group, topic, post, comment, and detail page interfaces support key-value pair filtering
- API: Posts list supports filtering by group and topic
- API: Content support for file mashups `[file:fid]`.
- API: group support for classified information
- API: post and comment list, support output sub-level multi-level content
- System: Proxy environment customization, new `.env` configuration `TRUSTED_PROXIES` multiple comma separated ones

### Fixes
- API: Key-value filtering feature does not work on some interfaces
- API: Boolean parameter false does not take effect
- API: Compatibility issue with client's messy language tag transfer
- Panel: Optimize import and export of blocking words to resolve compatibility issue

### Changed
- API: User count data of the list interface is cached
- System: System URLs remove backend configuration and use .env configuration values
- System: Refactored cache tag mechanism
- Framework: Updated laravel/framework to v10

### BREAKING CHANGES
- API: Posts and comments will remove the following parameter and migrate to the search interface
- API: Comment Data Structure remove `replyToUser` parameter, add `replyToComment` parameter
- API: document information remove `documentUrl` parameter, wrap `documentPreviewUrl` parameter URL assembly method `FileHelper::fresnsFileDocumentPreviewUrl()`.
- API: input-tips `common/input-tips` interface, remove nickname parameter
- Framework: PHP version at least 8.1 required


## 2.6.1 (2023-02-17)

### Fixes
- API: Blocking word interface parameter judgment syntax error
- API: Incorrect formatting of message list array in reverse order
- API: List interface key-value pair filter function error
- API: Complementary disabling parameter when content author is empty
- API: Supplement plugin unikey when configuration item plugin URL is empty
- Panel: Change data source description text of content type


## 2.6.0 (2023-02-16)

### Added
- Model: Audio and video support for processing path configuration
- API: Conversation message support for sorting parameters
- API: Content handling solution for user bans and logouts
- API: Various list interfaces support custom filter parameters
- Panel: Mentions can be enabled or disabled
- Panel: Hashtags can be enabled or disabled
- Framework: New developer mode (Dashboard->Settings)
- Framework: Compatible with reverse proxy deployment

### Fixes
- Install: Fix Windows installation failure
- API: Group posting permission string numbers causing permission validation not to be recognized
- API: Filter HTML tags on post and comment list pages to avoid summary truncation causing page structure conflicts
- API: Locally stored file domains do not use storage configuration
- API: Posting special rule date loop error
- API: Edit time limit issue and time format humanization
- API: Edit timeout and submit edits
- Helper: User model cache cleanup issue
- Helper: Cache time not allowed if file is anti-block

### Changed
- API: No cache model for account and user profile modifications to avoid modification failures
- API: Improved user friendly time calculation, added "year" unit configuration
- API: Private panels can get details
- Panel: Plugin page uploads are not supported by the storage plugin, the editor is configured to hide the option
- Panel: Error message output after plugin installation and update failure
- Framework: laravel/framework to v9.52.0
- Framework: fresns/plugin-manager to v2.4.5
- Framework: fresns/theme-manager to v2.1.2

### BREAKING CHANGES
- Data: Files table remove `video_gif_path` field
- Data: Files table field `video_cover_path` modified to `video_poster_path`


## 2.5.0 (2023-02-09)

### Added
- API: Notification messages support marking all types of messages as read
- API: Replies generated by the same comment will be notified again if the old notification has been read

### Fixes
- API: Delete notifications and session messages without flushing the user panel cache
- API: Notification message list `status` boolean 0 not accepted
- API: Comments can be added after deleting a post
- API: Issue with numeric values for editor configuration parameters
- Help: Cache cleanup for file usage
- Panel: Plugin uninstall failed
- Utilities: Fix logical file delete wrapper

### Changed
- Utilities: Optimise file uploads
- Framework: laravel/framework to v9.51.0
- Framework: fresns/cmd-word manager to v1.3.1
- Framework: fresns/plugin-manager to v2.4.2
- Framework: fresns/theme-manager to v2.1.1
- Framework: Use migration as upgrade solution for data changes

### BREAKING CHANGES
- Data: The fresns project no longer uses remote resources and can be used on the LAN.


## 2.4.0 (2023-02-01)

### Added
- API: Only the author is visible when a post or comment is disabled
- API: Add whitelist and blacklist CheckHeader middleware
- API: Quick post request returns draft ID or fsid
- Panel: Login to backend to passively trigger version detection

### Fixes
- API: Filter criteria for follow list must be in array format
- API: No tweet record in content, but also resolves @ symbols
- API: Cannot reply to your own posts after configuring reply permissions
- API: Newly posted content has negative humanisation time

### Changed
- Data: Changed callback query key from UUID to ULID
- API: topics have slug as unique value
- API: header login detection using blacklist mechanism
- API: Optimised file upload interface
- Panel: Plugin management, name link to App Market
- Frame: laravel/framework to v9.49.0
- Frame: fresns/plugin-manager to v2.4.0


## 2.3.1 (2023-01-21)

### Fixes
- Console: Fix upgrade command loading issue

### Changed
- Console: Data updates for reconfiguration and upgrade functions


## 2.3.0 (2023-01-21)

### Added
- Helper: Get plugin host `PluginHelper::fresnsPluginHostByUnikey($unikey);`

### Fixes
- Console: Int upgrade command cannot be executed
- Console: Main program timed task not executed

### Changed
- Console: Optimize command word schedule
- Helper: Modify artisan facades
- Helper: Adjust extended cache tag


## 2.2.0 (2023-01-20)

### Added
- Data: Added `disk` field to the files table
- Helper: Add file disk to file information
- Command: Add `storage:link` command to upgrade command

### Fixes
- API: Login error log count error determination
- Subscribe: Compatible subscribe are empty

### Changed
- Words: Refactor authentication path credentials command word
- Framework: The engine takes over the 404 page
- Frame: fresns/cmd-word-manager to v1.3.0
- Frame: fresns/plugin-manager to v2.3.4

### BREAKING CHANGES
- API: Refactor headers parameter naming to use `X-` prefix and camel-case naming


## 2.1.0 (2023-01-18)

### Added
- Helper: New clear cache by tag `CacheHelper::forgetFresnsTag();`
- Subscribe: support for subscribing to account and user login notifications
- Framework: Custom 404 page

### Fixes
- API: Fix role configuration cache
- API: The comment can't query to the post error
- API: Fix the problem of topic parsing failure in the last part of content
- API: Repair the error when the user's main role is empty
- API: Fix the problem of date and time format when there is no time zone
- Panel: Report error when installing plug-ins on command line

### Changed
- API: Sub-level comment list supports nested display
- API: Output to `[]` empty array format when tree structure data is empty
- API: Optimize the extraction and replacement of content topics
- Frame: laravel/framework to v9.48.0
- Frame: laravel/ui to v4.2.0
- Frame: fresns/plugin-manager to v2.3.3
- Frame: fresns/theme-manager to v2.0.8
- Frame: fresns/market-manager to v2.1.1


## 2.0.1 (2023-01-11)

### Changed
- Optimize cache tags


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
