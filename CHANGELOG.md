# Release Notes

All notable changes to this project will be documented in this file.

## 1.4.0 (2022-01-05)

**Bug Fixes**

- API: Inaccurate prompts when modifying registration closures
- API: Fix the boolean error problem of configuration parameters
- API: Data returns null by default when error is reported
- API: Uploading file tableId pass parameter processing problem

**Features**

- Frame: Composer upgrade to v2.2.3
- Frame: Laravel Framework upgrade to v8.78.0
- Frame: Bootstrap Icons upgrade to v1.7.2
- Database: Migrations and Seeders

## 1.3.0 (2021-11-13)

**Bug Fixes**

- API: repair the problem of uploading file tableId without conversion

**Features**

- Visual installation and upgrade
- Implement rules requirements for member nicknames and names
- Frame: Composer upgrade to v2.1.12
- Frame: Laravel Framework upgrade to v8.70.2
- Frame: Bootstrap Icons upgrade to v1.7.0

**BREAKING CHANGES**

- build: laravel migrations
- build: laravel seeders

## 1.2.0 (2021-11-01)

**Bug Fixes**

- API: fix the configuration interface can not turn the page problem
- API: fix the problem of wrong judgment of content editing permission
- API: fix the problem that the configuration information interface can't turn the page
- API: fix the problem of error in the comment list caused by the deletion of the main post

**Features**

- API: user profile interface, add user password and wallet password status parameters
- API: add time parameter to messages api
- API: add user verification
- API: member to modify the information interface, the avatar to pass the reference name change
    - avatarFileId modify to avatarFid
    - avatarFileUrl modify to avatarUrl
- API: uploading images to return to the reference increases imageConfigUrl and imageAvatarUrl
- Command word: user register avatarFileUrl modify to avatarUrl

## 1.1.0 (2021-10-28)

**Bug Fixes**

- API: correct posts and comments, icons output error
- API: correct comments list and details page, main posts anonymous information error
- API: corrected publication summary status change
- API: repair the post output of authority requirements, cut off according to percentage
- API: quickly publish a single image file, repair the suffix judgment
- API: fix transactionAmount parameter error

**Features**

- API: modify comment list interface child comment preview structure
- API: for interfaces involving member information, add the member's primary role "rid" parameter
- API: member list and detail add "followMeStatus" parameter
- Frame: upgrade to laravel framework 8.68.1

## 1.0.2 (2021-10-23)

**Bug Fixes**

- API: Post details page avatar getting error
- API: Fix the comment list main post author information error
- API: Quickly publish without notifying the plugin to process file errors
- API: Fix comment return to get member icon error
- Fresns Panel: Panel settings, after saving successfully, there is no prompt
- Fresns Panel: After deleting the administrator failed, there is no closure of the model, causing the page element to be blocked without clicking

**Features**

- API: Post and comment details page Markdown format does not resolve links
- API: View member information, if the viewer is himself, also output Mark status
- API: Increase the limit on the number of API requests per minute to 600
- API: For interfaces involving member information, add the verifiedDesc parameter
- Command word: The calculation scale of the long picture is adjusted to 3 times
- The built-in front-end icon font library Bootstrap Icons is upgraded to 1.6.1
- The built-in Base64 transcoder is upgraded to 3.7.2

## 1.0.1 (2021-10-18)

**Bug Fixes**

- API: Do not output the "not enabled" group categories and groups
- API: Get post list, pass searchGid as group uuid field, no data due to query id field
- API: Read the subsidiary table for the content parameter of the post and comment details page

## 1.0.0 (2021-10-15)

The first official version