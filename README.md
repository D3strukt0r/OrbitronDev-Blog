# Blog (OrbitronDev Service)

Get access to all blogs using an OrbitronDev account

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

What things you need to install the software and how to install them

```
Webserver (PHP 7.2+)
Database (e. g. MySql)
```

### Installing

A step by step series of examples that tell you have to get a development env running

Clone the project from github

```bash
$ git clone https://github.com/D3strukt0r/service-blog
```

Setup the project with composer

```bash
$ composer install --no-dev --optimize-autoloader
```

Next, rename `.env.dist` to `.env` and change following parameters:

```
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

* [Composer](https://getcomposer.org) - PHP Package manager
* [Symfony](https://symfony.com) - PHP Framework
* [Doctrine](https://www.doctrine-project.org) - PHP Database accessing
* [Twig](https://twig.symfony.com) - PHP Templating service
* [ReCaptcha](https://www.google.com/recaptcha) - Captcha service from Google
* [Bootstrap](https://getbootstrap.com) - Theme used in this service
* [Unify](https://wrapbootstrap.com/theme/unify-responsive-website-template-WB0412697) - Theme used in this service

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/D3strukt0r/service-blog/tags).

## Authors

* **Manuele Vaccari** - *Initial work* - Copied work from previous project [OrbitronDev](https://github.com/D3strukt0r/OrbitronDev)

See also the list of [contributors](https://github.com/D3strukt0r/service-blog/contributors) who participated in this project.

## License

This project is licensed under a copy of the MEGA Limited Code Review License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone who's code was used (Especially Stackoverflow)
