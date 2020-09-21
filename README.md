# OpenBlog

Get access to create blogs using an OpenID account

Project

[![License](https://img.shields.io/github/license/D3strukt0r/openblog)][license]
[![Docker Stars](https://img.shields.io/docker/stars/d3strukt0r/openblog-api-nginx.svg?label=docker%20stars%20(nginx))][docker-nginx]
[![Docker Pulls](https://img.shields.io/docker/pulls/d3strukt0r/openblog-api-nginx.svg?label=docker%20pulls%20(nginx))][docker-nginx]
[![Docker Stars](https://img.shields.io/docker/stars/d3strukt0r/openblog-api-php.svg?label=docker%20stars%20(php))][docker-php]
[![Docker Pulls](https://img.shields.io/docker/pulls/d3strukt0r/openblog-api-php.svg?label=docker%20pulls%20(php))][docker-php]

master-branch (alias stable, latest)

[![GH Action CI/CD](https://github.com/D3strukt0r/openblog/workflows/CI/CD/badge.svg?branch=master)][gh-action]
[![Coveralls](https://img.shields.io/coveralls/github/D3strukt0r/openblog/master)][coveralls]
[![Scrutinizer build status](https://img.shields.io/scrutinizer/build/g/D3strukt0r/openblog/master?label=scrutinizer%20build)][scrutinizer]
[![Scrutinizer code quality](https://img.shields.io/scrutinizer/quality/g/D3strukt0r/openblog/master?label=scrutinizer%20code%20quality)][scrutinizer]
[![Codacy grade](https://img.shields.io/codacy/grade/4b22c05ae79948ebb0757b29e45ad227/master?label=codacy%20code%20quality)][codacy]

<!-- develop-branch (alias nightly) -->

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

What things you need to install the software and how to install them

-   Webserver (PHP 7.2+)
-   Database (e. g. MySql)

### Installing

A step by step series of examples that tell you have to get a development env running

Clone the project from github

```shell
git clone https://github.com/D3strukt0r/openblog.git
```

Setup the project with composer

```shell
composer install --no-dev --optimize-autoloader
```

Next, rename `.env.dist` to `.env` and change following parameters:

```shell
RECAPTCHA_PUBLIC_KEY=... (ReCaptcha)
RECAPTCHA_PRIVATE_KEY=... (ReCaptcha)

OAUTH_CLIENT_ID="..." (OAuth2 Client from orbitrondev.org)
OAUTH_CLIENT_SECRET=... (OAuth2 Client from orbitrondev.org)
OAUTH_URL=... (Only needed if the account service is somewhere else) -> (Optional)

APP_ENV=prod
APP_SECRET=...

DATABASE_URL=... (Accessing databse)
```

## Built With

-   [PHP](https://www.php.net) - Programming Language
-   [Composer](https://getcomposer.org) - PHP Package manager
-   [Symfony](https://symfony.com) - PHP Framework
-   [Doctrine](https://www.doctrine-project.org) - PHP Database accessing
-   [Twig](https://twig.symfony.com) - PHP Templating service
-   [ReCaptcha](https://www.google.com/recaptcha) - Captcha service from Google
-   [Bootstrap](https://getbootstrap.com) - Theme used in this service
-   [Unify](https://wrapbootstrap.com/theme/unify-responsive-website-template-WB0412697) - Theme used in this service
-   [Github Actions](https://github.com/features/actions) - Automatic CI (Testing) / CD (Deployment)
-   [Docker](https://www.docker.com) - Building a Container for the Server

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/D3strukt0r/openblog/tags).

## Authors

-   **Manuele Vaccari** - [D3strukt0r](https://github.com/D3strukt0r) - _Initial work_

See also the list of [contributors](https://github.com/D3strukt0r/openblog/contributors) who participated in this project.

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE.txt](LICENSE.txt) file for details.

## Acknowledgments

-   Hat tip to anyone whose code was used
-   Inspiration
-   etc

[license]: https://github.com/D3strukt0r/openblog/blob/master/LICENSE.txt
[docker-nginx]: https://hub.docker.com/repository/docker/d3strukt0r/openblog-api-nginx
[docker-php]: https://hub.docker.com/repository/docker/d3strukt0r/openblog-api-php
[gh-action]: https://github.com/D3strukt0r/openblog/actions
[coveralls]: https://coveralls.io/github/D3strukt0r/openblog
[scrutinizer]: https://scrutinizer-ci.com/g/D3strukt0r/openblog/
[codacy]: https://app.codacy.com/manual/D3strukt0r/openblog/dashboard
