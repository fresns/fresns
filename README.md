<p align="center"><a href="https://fresns.org" target="_blank"><img src="https://raw.githubusercontent.com/fresns/docs/main/images/Fresns%20Logo.png" width="300"></a></p>

<p align="center">
<img src="https://img.shields.io/badge/Fresns-1.x-yellow" alt="Fresns">
<img src="https://img.shields.io/badge/PHP-%5E7.3%7C%5E8.0-blue" alt="PHP">
<img src="https://img.shields.io/badge/MySQL-%5E5.7%7C%5E8.0-orange" alt="MySQL">
<img src="https://img.shields.io/badge/License-Apache--2.0-green" alt="License">
</p>

## About Fresns

Fresns is a free and open source social network service software, a general-purpose community product designed for cross-platform, and supports flexible and diverse content forms. It conforms to the trend of the times, satisfies a variety of operating scenarios, is more open and easier to re-development.

- Users should read the [installation](https://fresns.org/guide/install.html) and [usage](https://fresns.org/guide/using.html) documentation.
- Extensions developers should read the [extension documentation](https://fresns.org/extensions/) and [database structure](https://fresns.org/database/).
- For client developers (web or app), please read the [API reference](https://fresns.org/api/) documentation.

## Fresns Framework

| Framework | Version | Use |
| --- | --- | --- |
| [Composer](https://github.com/composer/composer) | 2.1.11 | Application-Level Package Manager |
| [Laravel Framework](https://github.com/laravel/framework) | 8.69.0 | Framework |
| [Bootstrap](https://getbootstrap.com/) | 5.1.3 | Internal Front-end Framework |
| [Bootstrap Icons](https://icons.getbootstrap.com/) | 1.7.0 | Internal Icon Font Library |
| [jQuery](https://github.com/jquery/jquery) | 3.6.0 | Internal JS Framework |
| [Base64 JS](https://github.com/dankogai/js-base64) | 3.7.2 | Internal Base64 Transcoder |

| Database | Version |
| --- | --- |
| MySQL | 5.7 or 8.x |
| Collation | MySQL 5.7 `utf8mb4_unicode_520_ci`<br>MySQL 8.x `utf8mb4_0900_ai_ci` |
| Storage engine | InnoDB |

## Installation and Using

This repository is an R & D code repository without "vendor" reference library files. If you use this repository code package to install, you need to execute the composer command based on the command line to install "vendor" reference library files. If you feel troublesome, you can also download the full version of the installation package from the [official website](https://fresns.org/). The installation package on the official website already contains reference library files, so there is no need to perform command-line installation.

*Please make sure that the Composer package management tool is installed on your server.*

### Development deployment

1. Download the code package of the repository release, upload it to the business server and decompress it;
2. Execute the command line in the "main program root directory": `composer install`;
3. Rename the `.env.debug` file in the main program root directory to `.env`, and configure the database information according to [the installation tutorial](https://fresns.org/guide/install.html) on the official website;
4. The rest of the configuration process is the same as [the installation tutorial](https://fresns.org/guide/install.html) on the official website.

### Production deployment

1. Download the code package of this repository release and upload it to the business server to decompress;
2. Execute the command line in the "main program root directory": `composer install --optimize-autoloader --no-dev`;
3. Rename the `.env.example` file in the main program root directory to `.env`, and configure the database information according to [the installation tutorial](https://fresns.org/guide/install.html) on the official website;
4. The rest of the configuration process is the same as [the installation tutorial](https://fresns.org/guide/install.html) on the official website.

## Contributing

Thank you for considering contributing to the Fresns core library! The contribution guide can be found in the [Fresns documentation](https://fresns.org/community/join.html).

## Code of Conduct

In order to ensure that the Fresns community is welcoming to all, please review and abide by the [Code of Conduct](https://fresns.org/community/join.html#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Fresns, please send an e-mail to Taylor Otwell via [jarvis.okay@gmail.com](mailto:jarvis.okay@gmail.com). All security vulnerabilities will be promptly addressed.

## License

Fresns is open-sourced software licensed under the [Apache license](https://opensource.org/licenses/Apache-2.0).