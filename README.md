# ShellCommandBuilder

[![Latest Version](https://img.shields.io/github/release-pre/phpsu/shellcommandbuilder.svg?style=flat-square)](https://github.com/phpsu/shellcommandbuilder/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/phpsu/shellcommandbuilder/master.svg?style=flat-square)](https://travis-ci.org/phpsu/shellcommandbuilder)
[![Coverage Status](https://img.shields.io/codecov/c/gh/phpsu/shellcommandbuilder.svg?style=flat-square)](https://codecov.io/gh/phpsu/shellcommandbuilder)
[![Infection MSI](https://img.shields.io/endpoint?style=flat-square&url=https://badge-api.stryker-mutator.io/github.com/phpsu/ShellCommandBuilder/master)](https://infection.github.io)
[![Quality Score](https://img.shields.io/scrutinizer/g/phpsu/shellcommandbuilder.svg?style=flat-square)](https://scrutinizer-ci.com/g/phpsu/shellcommandbuilder)
[![Total Downloads](https://img.shields.io/packagist/dt/phpsu/shellcommandbuilder.svg?style=flat-square)](https://packagist.org/packages/phpsu/shellcommandbuilder)

Creating basic and more complex shell commands in an object-oriented fluid fashion.
This makes it very straight forward to abstract the general mechanisms of bash behind a readable and debuggable layer.

The Reference for this library is based on the [GNU Bash Reference Manual](https://www.gnu.org/savannah-checkouts/gnu/bash/manual/bash.html#Simple-Commands)
<br />If you need more features from that reference in this library, feel free to create an issue or pull request.

### Concept

Imagine you want to create the following bash command:
`a && b | c || d |& f && (g && h) || {i || j;}`

You can achieve that by creating a `ShellBuilder`-Object and then reading the command from left to right as instructions. 

```php
<?php

use PHPSu\ShellCommandBuilder\ShellBuilder;

$builder = new ShellBuilder();
$builder
    ->add('a')
    ->and('b')
    ->pipe('c')
    ->or('d')
    ->pipeWithForward('f')
    ->and(
        $builder->createGroup()->add('g')->and('h')
    )
    ->or(
        $builder->createGroup(true)->add('i')->or('j')
    );

```

