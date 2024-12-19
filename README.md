[![Latest Stable Version](https://poser.pugx.org/syde/phpcs/v)](https://packagist.org/packages/syde/phpcs)
[![License](https://poser.pugx.org/syde/phpcs/license)](https://github.com/inpsyde/phpcs/blob/HEAD/LICENSE)
[![PHP Version Require](http://poser.pugx.org/syde/phpcs/require/php)](https://packagist.org/packages/syde/phpcs)
[![PHP Quality Assurance Status](https://github.com/inpsyde/phpcs/actions/workflows/quality-assurance-php.yml/badge.svg)](https://github.com/inpsyde/phpcs/actions/workflows/quality-assurance-php.yml)
[![Coverage Status](https://coveralls.io/repos/github/inpsyde/phpcs/badge.svg)](https://coveralls.io/github/inpsyde/phpcs)

# Syde PHP Coding Standards

> Syde PHP coding standards for WordPress projects.

This package contains [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/) sniffs and rulesets to validate code developed for WordPress projects. It ensures code quality and adherence to coding conventions, especially the official [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/), as well as select best practices from the wider web development and PHP industries.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
  - [Local Installation](#local-installation)
  - [Global Installation](#global-installation)
  - [Verify Installation](#verify-installation)
- [Rulesets](#rulesets)
- [Usage](#usage)
  - [Command Line](#command-line)
  - [Custom Ruleset](#custom-ruleset)
  - [Customization](#customization)
  - [Auto-Fixing](#auto-fixing)
  - [Disabling or Excluding Rules](#disabling-or-excluding-rules)
  - [IDE Integration](#ide-integration)
- [Migrating from Inpsyde to Syde PHP Coding Standards](#migrating-from-inpsyde-to-syde-php-coding-standards)
- [Crafted by Syde](#crafted-by-syde)
- [Copyright and License](#copyright-and-license)
- [Contributing](#contributing)

---

## Requirements

The Syde PHP Coding Standards package requires:

- PHP 8.1+
- [Composer](https://getcomposer.org/)
- [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/) 3.11+
- [PHP_CodeSniffer Standards Composer Installer Plugin](https://github.com/PHPCSStandards/composer-installer/) 1.0+
- [PHPCompatibility](https://github.com/PHPCompatibility/PHPCompatibility/) 10+ (`dev-develop`)
- [PHPCSExtra](https://github.com/PHPCSStandards/PHPCSExtra/) 1.2+
- [PHPCSUtils](https://github.com/PHPCSStandards/PHPCSUtils/) 1.0+
- [Slevomat Coding Standard](https://github.com/slevomat/coding-standard/) 8.15
- [VariableAnalysis](https://github.com/sirbrillig/phpcs-variable-analysis/) 2.11+
- [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards/) 3.1+
- [WordPress VIP Coding Standards](https://github.com/Automattic/VIP-Coding-Standards/) 3.0+

When installed for local development, these packages will be installed as well:

- [PHPStan](https://github.com/phpstan/phpstan/) 2.0+
- [PHPUnit](https://github.com/sebastianbergmann/phpunit/) 10.5+

## Installation

Installing this package with [Composer](https://getcomposer.org/) will automatically install all required dependencies, and register the rulesets from the Syde PHP Coding Standards and other external standards with [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/) using the [PHP_CodeSniffer Standards Composer Installer Plugin](https://github.com/PHPCSStandards/composer-installer/).

### Local Installation

To install the Syde PHP Coding Standards, execute the following commands from the root of your project:

```shell
composer config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer require --dev syde/phpcs
```

### Global Installation

Alternatively, you can also install the Syde PHP Coding Standards globally:

```shell
composer global config allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
composer global require --dev syde/phpcs
```

### Verify Installation

You can verify the installation by executing the following command:

```shell
./vendor/bin/phpcs -i
```

This should display something like the following, including the Syde PHP Coding Standards (bold):

> The installed coding standards are MySource, PEAR, PSR1, PSR2, PSR12, Squiz, Zend, **Syde**, **Syde-Core**, **Syde-Extra**, **Syde-Templates**, WordPress-VIP-Go, WordPressVIPMinimum, PHPCompatibility, Modernize, NormalizedArrays, Universal, PHPCSUtils, VariableAnalysis, SlevomatCodingStandard, WordPress, WordPress-Core, WordPress-Docs and WordPress-Extra

## Rulesets

This package contains four rulesets:

- `Syde`: Complete set with all the sniffs defined in this package.
- `Syde-Core`: Minimum required rules for modern WordPress development at scale.
- `Syde-Extra`: Opinionated rules specific to formatting and other preferred coding practices at Syde; includes `Syde-Core`.
- `Syde-Templates`: Additional rules specific to PHP template files.

## Usage

### Command Line

Once the package has been installed via Composer, you can run the `phpcs` command-line tool on a given file or directory using the desired Syde PHP Coding Standard.

For example, this is how you can check a third-party plugin for minimum required rules only:

```shell
./vendor/bin/phpcs --standard=Syde-Core ./some-plugin/
```

Using the full `Syde` standard for a specific file would look like so:

```shell
./vendor/bin/phpcs --standard=Syde ./some-plugin/some-file.php
```

For more information on PHP_CodeSniffer usage, refer to the [documentation in the PHP_CodeSniffer Wiki](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Usage).

### Custom Ruleset

Like any other PHP_CodeSniffer standard, you can add the Syde PHP Coding Standard(s) to a custom PHP_CodeSniffer ruleset (e.g., a `phpcs.xml.dist` file).

A minimum working example could look like so:

```xml
<?xml version="1.0"?>
<ruleset>
    <!-- Minimum required Syde PHP Coding Standard rules. -->
    <rule ref="Syde-Core" />

    <!-- Secondary standard. -->
    <rule ref="MyCompanyStandard" />

</ruleset>
```

Using a custom ruleset avoids passing many arguments via the command line, and at the same time ensures consistent usage. All you have to do then is to run the `phpcs` command-line tool:

```shell
./vendor/bin/phpcs
```

Any argument or option you pass, will overwrite what's been defined in the custom ruleset.

Here is a real-world example including files and folders to check, as well as some PHP_CodeSniffer and ruleset configuration:

```xml
<?xml version="1.0"?>
<ruleset>
    <!-- Check for cross-version support for PHP 8.1 and higher. -->
    <config name="testVersion" value="8.1-" />

    <!-- Check for correct text domain usage. -->
    <config name="text_domain" value="my-project" />

    <file>./src</file>
    <file>./templates</file>
    <file>./tests</file>
    <file>./index.php</file>

    <!-- Use colors, and show sniff error codes and progress. -->
    <arg name="colors" />
    <arg value="sp" />

    <!-- Recommended Syde PHP Coding Standard rules. -->
    <rule ref="Syde-Extra" />

    <!-- Template-specific rules. -->
    <rule ref="Syde.ControlStructures.DisallowElse">
        <exclude-pattern>*/templates/*</exclude-pattern>
    </rule>
    <rule ref="Syde-Templates">
        <include-pattern>*/templates/*</include-pattern>
    </rule>

    <!-- Do not report on function length for tests. -->
    <rule ref="Syde.Functions.FunctionLength">
        <exclude-pattern>*/tests/cases/*</exclude-pattern>
    </rule>

    <!-- PSR-4 namespace configuration. -->
    <rule ref="SlevomatCodingStandard.Files.TypeNameMatchesFileName">
        <properties>
            <property name="rootNamespaces" type="array">
                <element key="src" value="MyCompany\MyProject" />
                <element key="tests/cases" value="MyCompany\MyProject\Tests" />
                <element key="tests/src" value="MyCompany\MyProject\Tests" />
            </property>
        </properties>
    </rule>

    <!-- Secondary standard. -->
    <rule ref="MyCompanyStandard" />

</ruleset>
```

For more information, take a look at [Using a Default Configuration File](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Advanced-Usage#using-a-default-configuration-file) and the [Annotated Ruleset](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki/Annotated-Ruleset) pages in the [PHP_CodeSniffer Wiki](https://github.com/PHPCSStandards/PHP_CodeSniffer/wiki).

### Customization

The Syde PHP Coding Standards contain a number of sniffs that are configurable. This means that you can turn parts of the sniff on or off, or change the behavior by setting a property for the sniff in your custom ruleset file.

You can find a complete list of all the properties you can change for the Syde PHP Coding Standards in the [list of all sniffs](docs/Sniffs.md).

### Auto-Fixing

The Syde PHP Coding Standards include several sniffs that support automatic fixing of coding standard violations. These sniffs are marked with the 🔧 symbol in the [list of all sniffs](docs/Sniffs.md). To fix your code automatically, run the `phpcbf` command-line tool instead of `phpcs`:

```shell
./vendor/bin/phpcbf --standard=Syde-Extra ./some-file.php
```

Always remember to back up your code before performing automatic fixes. Also, make sure to manually check the updated code as the automatic fixer can sometimes produce unwanted results.

### Disabling or Excluding Rules

For information about disabling or excluding rules, refer to the [Disabling or Excluding Rules](docs/Disabling.md) page in the `docs/` folder.

### IDE Integration

For information about IDE integration (currently PhpStorm only), refer to the [IDE Integration](docs/Integration.md) page in the `docs/` folder.

## Migrating from Inpsyde to Syde PHP Coding Standards

In case you are already using the Inpsyde PHP Coding Standards (version 1 or 2) and want to migrate to the Syde PHP Coding Standards, refer to the [Migration](docs/Migration.md) page in the `docs/` folder.

## Crafted by Syde

The team at [Syde](https://syde.com/) is engineering the Web since 2006.

## Copyright and License

This package is [free software](https://www.gnu.org/philosophy/free-sw.en.html) distributed under the terms of the GNU General Public License version 2 or (at your option) any later version. For the full license, see [LICENSE](./LICENSE).

## Contributing

All contributions are very welcome. Please read the [CONTRIBUTING](https://github.com/inpsyde/.github/blob/HEAD/CONTRIBUTING.md) documentation to get started.

By contributing code, you grant its use under the current license (see [LICENSE](./LICENSE)).
