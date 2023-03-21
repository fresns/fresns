<p align="center"><a href="https://fresns.org" target="_blank"><img src="https://raw.githubusercontent.com/fresns/docs/main/images/Fresns-Logo(orange).png" width="300"></a></p>

<p align="center">
<img src="https://img.shields.io/badge/PHP-%5E8.1-green" alt="PHP">
<img src="https://img.shields.io/badge/MySQL-%5E5.7%7C%5E8.0-orange" alt="MySQL">
<img src="https://img.shields.io/badge/License-Apache--2.0-blue" alt="License">
</p>

## About Fresns

Fresns is a free and open source social network service software, a general-purpose community product designed for cross-platform, and supports flexible and diverse content forms. It conforms to the trend of the times, satisfies a variety of operating scenarios, is more open and easier to re-development.

- Users should read the [installation](https://fresns.org/guide/install.html) and [operating](https://fresns.org/guide/operating.html) documentation.
- Extensions developers should read the [extension documentation](https://fresns.org/extensions/) and [database structure](https://fresns.org/database/).
- For client developers (web or app), please read the [API reference](https://fresns.org/api/) documentation.

## Server Requirements

| Environment | Requirements |
| --- | --- |
| Package Manager | Composer 2.5 or higher |
| PHP Version | PHP 8.1 or higher |
| PHP Extensions | `fileinfo` |
| PHP Functions | `putenv` `symlink` `proc_open` `passthru` |
| Database Version | MySQL 5.7 or 8.x |

## Installation and Using

This repository is an R & D code repository without "vendor" reference library files. If you use this repository code package to install, you need to execute the composer command based on the command line to install "vendor" reference library files. If you feel troublesome, you can also download the full version of the installation package from the [official website](https://fresns.org/guide/install.html). The installation package on the official website already contains reference library files, so there is no need to perform command-line installation.

**Deployment Process**

1. Download the code package of [the repository release](https://github.com/fresns/fresns/releases), upload it to the business server and decompress it.
2. Execute the command line in the "main program root directory", Download the vendor package file.
    - Development deployment: `composer install`
    - Production deployment: `composer install --optimize-autoloader --no-dev`
3. Execute the php artisan command under the "main program root directory" to configure the manager.
    - `php artisan vendor:publish --provider="Fresns\PluginManager\Providers\PluginServiceProvider"`
    - `php artisan vendor:publish --provider="Fresns\ThemeManager\Providers\ThemeServiceProvider"`
    - `php artisan vendor:publish --provider="Fresns\MarketManager\Providers\MarketServiceProvider"`
4. Configure the web server according to the [the installation tutorial](https://fresns.org/guide/install.html) on the official website.
5. Visit the `/install` page to do the installation.

## Contributing

Thank you for considering contributing to the Fresns core library! The contribution guide can be found in the [Fresns documentation](https://fresns.org/community/join.html).

## Code of Conduct

In order to ensure that the Fresns community is welcoming to all, please review and abide by the [Code of Conduct](https://fresns.org/community/join.html#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Fresns, please send an e-mail to Taylor Otwell via [support@fresns.org](mailto:support@fresns.org). All security vulnerabilities will be promptly addressed.

## License

Fresns is open-sourced software licensed under the [Apache-2.0 license](https://github.com/fresns/fresns/blob/main/LICENSE).
