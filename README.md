# ShellCommandBuilder

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

