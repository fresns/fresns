<p align="center"><a href="https://fresns.org" target="_blank"><img src="https://raw.githubusercontent.com/fresns/docs/main/images/Fresns-Logo(orange).png" width="300"></a></p>

<p align="center">
<img src="https://img.shields.io/badge/PHP-%5E8.0-green" alt="PHP">
<img src="https://img.shields.io/badge/MySQL-%5E8.0-orange" alt="MySQL">
<img src="https://img.shields.io/badge/License-Apache--2.0-blue" alt="License">
</p>

## About Fresns

Fresns is a free and open source social network service software, a general-purpose community product designed for cross-platform, and supports flexible and diverse content forms. It conforms to the trend of the times, satisfies a variety of operating scenarios, is more open and easier to re-development.

- Users should read the [installation](https://fresns.org/guide/install.html) and [operating](https://fresns.org/guide/operating.html) documentation.
- Extensions developers should read the [extension documentation](https://fresns.org/extensions/) and [database structure](https://fresns.org/database/).
- For client developers (web or app), please read the [API reference](https://fresns.org/api/) documentation.

## Fresns Framework

| Framework | Version | Use |
| --- | --- | --- |
| [Composer](https://github.com/composer/composer) | 2.2.9 | Application-Level Package Manager |
| [Laravel Framework](https://github.com/laravel/framework) | 8.83.5 | Framework |
| [Laravel Lang](https://github.com/Laravel-Lang/lang) | 10.4.11 | Framework Lang Resources |
| [Laravel Excel](https://github.com/SpartnerNL/Laravel-Excel) | 3.1.37 | Excel exports and imports |
| [PhpZip](https://github.com/Ne-Lexa/php-zip) | 4.0.1 | ZIP archives php library |
| [Bootstrap](https://getbootstrap.com/) | 5.1.3 | Internal Front-end Framework |
| [Bootstrap Icons](https://icons.getbootstrap.com/) | 1.8.1 | Internal Icon Font Library |
| [jQuery](https://github.com/jquery/jquery) | 3.6.0 | Internal JS Framework |
| [Select2](https://github.com/select2/select2) | 4.1.0 | Internal Select2 Boxes |
| [Base64 JS](https://github.com/dankogai/js-base64) | 3.7.2 | Internal Base64 Transcoder |

| Environment | Requirements |
| --- | --- |
| PHP Extensions | `fileinfo` |
| PHP Functions | `putenv` `symlink` `readlink` `proc_open` `shell_exec` `exec` |

| Database | MySQL 8.x |
| --- | --- |
| Collation | `utf8mb4_0900_ai_ci` |
| Storage engine | InnoDB |

## Installation and Using

This repository is an R & D code repository without "vendor" reference library files. If you use this repository code package to install, you need to execute the composer command based on the command line to install "vendor" reference library files. If you feel troublesome, you can also download the full version of the installation package from the [official website](https://fresns.org/). The installation package on the official website already contains reference library files, so there is no need to perform command-line installation.

**Deployment Process**

1. Download the code package of [the repository release](https://github.com/fresns/fresns/releases), upload it to the business server and decompress it.
2. Configure the web server according to the [the installation tutorial](https://fresns.org/guide/install.html) on the official website.
3. Execute the command line in the "main program root directory".
    - Development deployment: `composer install`
    - Production deployment: `composer install --optimize-autoloader --no-dev`
4. The rest of the configuration process is the same as [the installation tutorial](https://fresns.org/guide/install.html) on the official website.

*Please make sure that the Composer package management tool is installed on your server.*

## Contributing

Thank you for considering contributing to the Fresns core library! The contribution guide can be found in the [Fresns documentation](https://fresns.org/community/join.html).

## Code of Conduct

In order to ensure that the Fresns community is welcoming to all, please review and abide by the [Code of Conduct](https://fresns.org/community/join.html#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Fresns, please send an e-mail to Taylor Otwell via [support@fresns.org](mailto:support@fresns.org). All security vulnerabilities will be promptly addressed.

## License

Fresns is open-sourced software licensed under the [Apache-2.0 license](https://github.com/fresns/fresns/blob/main/LICENSE).