# ShellCommandBuilder

[![Latest Version](https://img.shields.io/github/release-pre/phpsu/shellcommandbuilder.svg?style=flat-square)](https://github.com/phpsu/shellcommandbuilder/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/phpsu/shellcommandbuilder/master.svg?style=flat-square)](https://travis-ci.org/phpsu/shellcommandbuilder)
[![Coverage Status](https://img.shields.io/codecov/c/gh/phpsu/shellcommandbuilder.svg?style=flat-square)](https://codecov.io/gh/phpsu/shellcommandbuilder)
[![Type Coverage Status](https://shepherd.dev/github/phpsu/ShellCommandBuilder/coverage.svg)](https://github.com/phpsu/shellcommandbuilder)
[![Infection MSI](https://img.shields.io/endpoint?style=flat-square&url=https://badge-api.stryker-mutator.io/github.com/phpsu/ShellCommandBuilder/master)](https://infection.github.io)
[![Quality Score](https://img.shields.io/scrutinizer/g/phpsu/shellcommandbuilder.svg?style=flat-square)](https://scrutinizer-ci.com/g/phpsu/shellcommandbuilder)
[![Total Downloads](https://img.shields.io/packagist/dt/phpsu/shellcommandbuilder.svg?style=flat-square)](https://packagist.org/packages/phpsu/shellcommandbuilder)

Creating basic and more complex shell commands in an fluid object-oriented fashion.
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

### Table of Contents

1. [Installation](#installation)
2. [Usage](#usage)
    1. [Simple Commands](#simple-commands)
    2. [Pipelines, Lists, and Redirections](#pipelines-lists-and-redirections)
    4. [Complex Commands](#complex-commands)
    5. [Conditional Expressions](#conditional-expressions)
    6. [Coprocess](#coprocess)
3. [Specials](#special)
4. [Contributing](#contributing)
5. [Testing](#testing)

### Installation

You can use this library in your project by adding it with composer:

`composer require phpsu/shellcommandbuilder`

Then include it in your class/file.

```php
<?php

use PHPSu\ShellCommandBuilder\ShellBuilder;

$builder = new ShellBuilder();
``` 
### Introduction

This library is boiled down to these three main components:
- ShellBuilder
- ShellCommand
- ShellWord

The ShellBuilder is the glue that holds a collection of commands together.
The glue is one of the control operators like `||` or `&&`.<br/>
Commands are represented by the ShellCommand-Class.
The ShellCommand is responsible for the arguments and options etc.<br />
A ShellCommand is composed of ShellWords, they represent the tokens that make up a command.

Let's look at an example:
```shell script
echo "hello world" | grep -e "world"
```
This entire line is a ShellBuilder-Object containing the two ShellCommands:
- `echo "hello world`
- `grep -e "world"`

Those are connected with the `|`-Operator <br />
Taking apart each of those commands returns the following ShellWords:

| executable | arguments | options |
| ---------:|---------:|-------------:|
| `echo`, `grep` | `hello world` | `-e "world"` |

### Usage
#### Simple Commands

Much of the API is marked internal, it is meant to be accessed through the `ShellBuilder`-Class.
<br />This should make it very straight-forward to build simple and more complex commands from one basis.<br />
Additionally, the `ShellBuilder` has factory-style methods that help building commands top to bottom in an instant.

That means, creating a `ShellBuilder` can look like this:
```php
$builder = new ShellBuilder();
```
or like this:
```php
$builder = ShellBuilder::new();
```
A ShellCommand can be created like this:
```php
$command = ShellBuilder::command('name-of-command');
```
or, if there is already a ShellBuilder-object available, like this
```php
/** @var \PHPSu\ShellCommandBuilder\ShellBuilder $builder */
$builder->createCommand('name-of-command');
```

Let's take a look at the command from earlier and build it step by step.
```shell script
echo "hello world" | grep -e "world"
```

> Note: each step is written into the code as comment
```php
<?php
use PHPSu\ShellCommandBuilder\ShellBuilder;


// 1. First we create the command `echo`
$echo = ShellBuilder::command('echo');

// 2. "hello world" is an argument, that we can add like this:
$echo->addArgument('Hello World');

// 3. we create the `grep` command
$grep = ShellBuilder::command('grep');

// 4. and add the option '-e "world"'.
// the single hyphen that is before the option "e" marks it as a *short* option (addShortOption)
// Having two hyphens like --color makes it a regular option (addOption)
$grep->addShortOption('e', 'world');

// 5. Now we need combine those two commands together
// We do that, by creating a ShellBuilder
$builder = ShellBuilder::new();

// 6. And then adding the echo-command into it
$builder->add($echo);

// 7. Earlier we saw, that these two commands where held together by the pipe-Operator
// This can be accomplished by using the pipe-Method 
$builder->pipe($grep);

// 8. To use this command in e.g. shell_exec, you can convert it into a string and use it
shell_exec((string)$builder); // -> echo 'hello world' | echo -e 'world'
```
> Note: Every argument and option is escaped by default.

All methods implement the fluent interface.
For this library that means that you can rewrite the example above by chaining everything together:

```php
<?php

use PHPSu\ShellCommandBuilder\ShellBuilder;

$builder = ShellBuilder::new()
    ->createCommand('echo')
    ->addArgument('Hello World')
    ->addToBuilder()
    ->pipe(
        ShellBuilder::command('grep')
            ->addShortOption('e', 'world')            
    );
shell_exec((string)$builder); // -> echo 'hello world' | echo -e 'world'
```

The `createCommand` passes the current ShellBuilder into the ShellCommand-Instance.
Through `addToBuilder` that ShellBuilder can be accessed again, and the command is automatically added to the ShellBuilder.
This currently only works for `and`.  

#### Pipelines, Lists, and Redirections

The ShellBuilder is a representation of what holds commands together.
Whether it is to execute commands sequentially, or to connect input and output.

Let's look at this following fake example:<br/>
`a; b && c | d || e |& f 2>&1`<br />
It illustrates the various ways of connecting commands together.

Rebuilding this command could look like this: 

```php
<?php

use PHPSu\ShellCommandBuilder\ShellBuilder;

$builder = new ShellBuilder();
// adding the initial command
$builder->add('a');
// adding the next command for `;`
$builder->add('b');
// combining with and --> `&&` 
$builder->and('c');
// piping the output --> `|`
$builder->pipe('d');
// combining with or --> `||`
$builder->or('e');
// piping the output including the error --> `|&`
$builder->pipeWithForward('f');
// redirect stderr to stdout --> `2>&1`
$builder->redirectErrorToOutput();
```

The full list of methods can be found here: [API Docs](/docs/api.md)

#### Complex Commands

The idea behind this library is to make generating larger and complex shell commands more readable and maintainable.<br />
The following example is taken out of PHPsu. This command syncs a database from a remote source to a local database.

```shell script
ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false -h '\''database'\'' -u '\''root'\'' -p '\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h '127.0.0.1' -P 2206 -u 'root' -p 'root'
```

First, we have to think about the components that this command is composed of.
This results in these commands:
```shell script
ssh -F 'php://temp' 'hostc'

mysqldump --opt --skip-comments --single-transaction --lock-tables=false -h 'database' -u 'root' -p 'root' 'sequelmovie'

echo 'CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'

cat

mysql -h '127.0.0.1' -P 2206 -u 'root' -p 'root'
```

Now, we build this in PHP:

```php
<?php

use PHPSu\ShellCommandBuilder\ShellBuilder;

$builder = new ShellBuilder();
// creating the first command.
// The 'true' removes the connection between ShellBuilder and ShellCommand and makes it anonymous.
// This is the same result as ShellBuilder::command()
$mysqlDump = $builder->createCommand('mysqldump', true)
// adding the options and short-options
    ->addOption('opt')
    ->addOption('skip-comments')
    ->addOption('single-transaction')
// the signature of options have four variables
// 'lock-tables' is the name of the option --> "--lock-tables"
// the string 'false' is the value --> "--lock-tables 'false'"
// the third variable disables escaping --> "--lock-tables false"
// the fourth variable turns the space between name and value into '=' --> "--lock-tables=false"
    ->addOption('lock-tables', 'false', false, true)
    ->addShortOption('h', 'database')
    ->addShortOption('u', 'root')
    ->addShortOption('p', 'root')
    ->addArgument('sequelmovie')
    ->addToBuilder();
$builder->createCommand('ssh')
    ->addShortOption('F', 'php://temp')
    ->addArgument('hostc')
// SubCommand is technically an argument, that always escapes the output
    ->addSubCommand(
        $mysqlDump->pipe(
// 'createGroup' flags a ShellBuilder to wrap the commands in braces e.g (echo "hello world")
            $mysqlDump->createGroup()
            ->createCommand('echo')
            ->addArgument('CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;')
            ->addToBuilder()
            ->and('cat')
        )
    )
    ->addToBuilder()
    ->pipe(
        $builder->createCommand('mysql')
            ->addShortOption('h', '127.0.0.1')
// disabling escaping here: --> "-P 2206"
            ->addShortOption('P', '2206', false)
            ->addShortOption('u', 'root')
            ->addShortOption('p', 'root')
    )
;
```

Next, we take a look at how to achieve process and command substition.
The following is again a mock example.
It creates a list of all php-files in the current and all below directories, sorted and enriched with the size.
This file-list is redirected into a txt-file with the current month-name as filename.

```shell script
cat <(ls -1ARSsD | grep ".*\.php") >> $(date +%B).txt
```

And this is how it could look like in php:

```php

use PHPSu\ShellCommandBuilder\ShellBuilder;

$builder = ShellBuilder::new()
    ->createCommand('cat')
// the false at the end prints the argument unescaped
    ->addArgument(
        ShellBuilder::new()
// turning all commands within this builder into a process substitution --> <(command ...)
// the same would work with `createCommandSubstition` resulting in something like this $(command ...)
        ->createProcessSubstition()
        ->createCommand('ls')
// currently combining short-options has to be done manually, although it could change in the future
// but doing it like this will always be possible, since it's impossible to evaluate the correctness
// without having the man-page of all the commands available
        ->addShortOption('1ARSsD')
        ->addToBuilder()
        ->pipe(
            ShellBuilder::command('grep')
            ->addArgument('.*\.php')
        ),
        false
    )
    ->addToBuilder()
// redirects stdout from the previous command and pushes it into stdin of the next command
// if redirected into a file, the true at the end changes the type to appending instead of overwriting --> "a >> b"
    ->redirectOutput(
        ShellBuilder::new()
            ->createCommand('date')
            ->addArgument('+%B', false)
// this is similar to the process/command-substitition from above but here it is applied on a command instead
// toggling means that instead of taking true or false as an argument it flips the internal state back and forth
            ->toggleCommandSubstitution()
            ->addToBuilder()
            ->addFileEnding('txt'),
        true
    )
;
```

#### Conditional Expressions

Conditional Expressions are currently a work in progress. The basic API stands, but the overall usage might change, especially when it comes down to escaping.

There are multiple conditional-expression-types that can be used to built expressions.
They are build upon the [Shell-Syntax Bash Reference](https://www.gnu.org/software/bash/manual/html_node/Bash-Conditional-Expressions.html).

The following expression-types exist:
- Artihmetic: ArithmeticExpression::class
- File: FileExpression::class
- Shell: ShellExpression::class
- String: StringExpression::class

Let's look at two examples:
- 1: Only executing a command, if a file is not empty
- 2: Only executing a command, if a variable is greater than 5

```shell script

# 1:
[[ -s test.php ]] && echo "hello";

# 2: 
a=6; [[ "$a" -gt "5" ]] && echo "hello";

# 3: 
a=`cat file.txt`; [[ "$a" -gt "5" ]] && echo "hello";
```

```php

use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPSu\ShellCommandBuilder\Conditional\FileExpression;
use PHPSu\ShellCommandBuilder\Conditional\ArithmeticExpression;

# 1:
ShellBuilder::new()
    ->add(FileExpression::create()->notEmpty('test.php'))
    ->and(ShellBuilder::command('echo')->addArgument('hello'))
;

# 2:
ShellBuilder::new()
    // adding a variable "a" with the value "6"
    // the third argument replaces $() through backticks --> a=$(cat) ~> a=`cat`
    // the fourth argument sets escpaing to false.
    // Escaping is disabled for commands as value.
    ->addVariable('a', '6', false, false)
    ->add(ArithmeticExpression::create()->greater('$a', '5'))
    ->and(ShellBuilder::command('echo')->addArgument('hello'))
;

# 3:

ShellBuilder::new()
    ->addVariable('a',
        ShellBuilder::new()
        ->createCommand('cat')
        ->addNoSpaceArgument('file')
        ->addToBuilder()
        ->addFileEnding('txt'),
        true // enable backticks
    )
    ->add(ArithmeticExpression::create()->greater('$a', '5')->escapeValue(true))
    ->and(ShellBuilder::command('echo')->addArgument('hello'))
;

```

#### Coprocess

To run commands in the background, the ShellBuilder class supports the `coproc` keyword.
<br /> This keyword lets the command run asynchronously in a subshell and can be combined with pipes and redirections.

More information on Coprocesses can be found [in the Bash Reference](https://www.gnu.org/software/bash/manual/html_node/Coprocesses.html).

Let's look at an example:
`{coproc tee {tee logfile;} >&3 ;} 3>&1`
<br />
This starts `tee` in the background and redirects its output to stdout

```php

use PHPSu\ShellCommandBuilder\Definition\GroupType;
use PHPSu\ShellCommandBuilder\ShellBuilder;

// we first create a new ShellBuilder, that will be wrapped in the group-syntax that does not open a subshell
// -> { command-list ;}
$builder = new ShellBuilder(GroupType::SAMESHELL_GROUP);
// then we set that builder to be asynchronous.
// the second argument of this method gives the coprocess a name.
// default is no name
// -> coproc [NAME] command
$builder->runAsynchronously(true)
    ->createCommand('tee')
    ->addArgument(
// createGroup again wraps it into a group-syntax and the true indicates, that is is in the same-shell notation
// false would open a subshell like e.g ( command ).
// default is false
        $builder->createGroup(true)
            ->createCommand('tee')
            ->addArgument('logfile', false)
            ->addToBuilder(),
        false
    )
    ->addToBuilder()
// redirectDescriptor is the more powerful way of writing redirects between File Descriptors
// argument 1: command that we redirect from/to
// argument 2: direction of the redirect (true: >&, false <&)
// argument 3: file descriptor before redirection
// argument 4: file descriptor after redirection
// the example below would render: >&3
    ->redirectDescriptor('', true, null, 3);
ShellBuilder::new()->add($builder)->redirectDescriptor('', true, 3, 1);            
```

If you want to direct a single command or a list of commands into the background, you can achieve that by appending an ampersand `&` at the end of a command.

So maybe you want to do this:
`./import-script & ./import-script2 &`

Then, this can be achieved like this:
```php
<?php
use PHPSu\ShellCommandBuilder\ShellBuilder;

ShellBuilder::new()->add('./import-script')->async('./import-script2')->async();
```

### Special

#### Pattern-Class - ShellWord parsing

The pattern-class validates string inputs as valid Bourne Shellwords.
It is based on its equivalent implementations in the [Ruby](https://ruby-doc.org/stdlib-2.5.1/libdoc/shellwords/rdoc/Shellwords.html) and Rust languages.
<br/>
It takes a string and applies the word parsing rules of shell to split it into an array.

```php
use PHPSu\ShellCommandBuilder\Definition\Pattern;

Pattern::split('three blind mice');
// ['three', 'blind', 'mice']
```

Pattern::split respects escaping and quoting and only splits outside of these:

```php
use PHPSu\ShellCommandBuilder\Definition\Pattern;

Pattern::split('/home/user/dev/hallo\ welt.txt');
// ['/home/user/dev/hallo welt.txt']

Pattern::split('a "b b" a');
// ['a', 'b b', 'a']
```

The method will throw an exception if there is an invalid input.
<br />
For example the following has an unmatched quoting:

```php
use PHPSu\ShellCommandBuilder\Definition\Pattern;
Pattern::split("a \"b c d e");
// ShellBuilderException::class
// The given input has mismatching Quotes
```

#### Debugging the ShellBuilder

Sometimes there is a need to better understand why the output is rendered the way it is.
<br />
For those situations, all classes implement a `__toArray()`-method, that take the current class-state and print it as an array.
The `ShellBuilder` additionally implements `jsonSerializable`.
It itself calls the `__toArray`-method and is meant as a shortcut for outputting to a client.

If you call `__toArray()` on a ShellBuilder, it will go through all commands and turn them into an array too.
That way you have a deeply nested structure, that represents the list of commands you want to execute. 

### Contributing

install for contributing
````bash
git clone git@github.com:phpsu/ShellCommandBuilder.git
cd ShellCommandBuilder
composer install
````
    
### Testing

````bash
composer test
````

You can also check, whether any changes you made are affecting your tests immediately on save:
````bash
composer test:watch
````

Type-Checking is being done with psalm.
````bash
composer psalm
````

If you see a low `Mutation Score Indicator (MSI)` value, you can show the mutations that are escaping:
````bash
composer infection -- -s
````

## Security

Email `git@cben.co` if you discover any security related issues.

## Credits

- [Chris Ben](https://github.com/ChrisB9)
- [Matthias Vogel](https://github.com/Kanti)

## License

The MIT License (MIT). Please see [License File](https://github.com/phpsu/phpsu/blob/master/LICENSE) for more information.
