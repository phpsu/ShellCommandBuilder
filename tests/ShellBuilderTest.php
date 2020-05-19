<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests;

use PHPSu\ShellCommandBuilder\Conditional\ArithmeticExpression;
use PHPSu\ShellCommandBuilder\Conditional\FileExpression;
use PHPSu\ShellCommandBuilder\Conditional\StringExpression;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPUnit\Framework\TestCase;

final class ShellBuilderTest extends TestCase
{
    public function testBuilderConcept(): void
    {
        $result = 'a && b | c || d |& f && (g && h) || {i || j;}';
        $builder = new ShellBuilder();
        $a = $builder->createCommand('a');
        $b = $builder->createCommand('b');
        $c = $builder->createCommand('c');
        $d = $builder->createCommand('d');
        $f = $builder->createCommand('f');
        $g = $builder->createCommand('g');
        $h = $builder->createCommand('h');
        $i = $builder->createCommand('i');
        $j = $builder->createCommand('j');

        $builder
            ->add($a)
            ->and($b)
            ->pipe($c)
            ->or($d)
            ->pipeWithForward($f)
            ->and(
                $builder->createGroup()->add($g)->and($h)
            )
            ->or(
                $builder->createGroup(true)->add($i)->or($j)
            );
        $this->assertEquals($result, (string)$builder);
    }

    public function testBuilderConceptWithShortcut(): void
    {
        $result = 'a && b | c || d |& f && (g && h) || {i || j;}';
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
        $this->assertEquals($result, (string)$builder);
    }

    public function testCommandListDelimiter(): void
    {
        $result = (string)(new ShellBuilder())->add('a')->add('b')->add('c');
        $this->assertEquals('a ; b ; c', $result);
    }

    public function testCommandListAnd(): void
    {
        $result = (string)(new ShellBuilder())->add('a')->and('b')->and('c');
        $this->assertEquals('a && b && c', $result);
    }

    public function testCommandListOr(): void
    {
        $result = (string)(new ShellBuilder())->add('a')->or('b')->or('c');
        $this->assertEquals('a || b || c', $result);
    }

    public function testCommandPipe(): void
    {
        $result = (string)(new ShellBuilder())->add('a')->pipe('b')->pipe('c');
        $this->assertEquals('a | b | c', $result);
    }

    public function testCommandPipeForward(): void
    {
        $result = (string)(new ShellBuilder())->add('a')->pipeWithForward('b')->pipeWithForward('c');
        $this->assertEquals('a |& b |& c', $result);
    }

    public function testComplexSshCommandCreation(): void
    {
        $builder = new ShellBuilder();
        $mysqldump = $builder->createCommand('mysqldump')
            ->addOption('opt')
            ->addOption('skip-comments')
            ->addOption('single-transaction')
            ->addOption('lock-tables', 'false', false, true)
            ->addShortOption('h', '127.0.0.1')
            ->addShortOption('u', 'test')
            ->addShortOption('p', 'aaaaaaaa')
            ->addArgument('testdb');
        $inlineBuilder = new ShellBuilder();
        $inlineBuilder->add($mysqldump)
            ->pipe(
                $inlineBuilder->createGroup()
                ->add(
                    $inlineBuilder->createCommand('echo')
                    ->addArgument('CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;')
                )->and('cat')
            );
        $ssh = $builder->createCommand('ssh')
            ->addShortOption('F', '.phpsu/config/ssh_config')
            ->addArgument('projectEu')
            ->addSubCommand($inlineBuilder);
        $builder->add($ssh)
            ->pipe(
                $builder->createCommand('mysql')
                ->addShortOption('h', '127.0.0.1')
                ->addShortOption('u', 'root')
                ->addShortOption('p', 'root')
            );
        $result = "ssh -F '.phpsu/config/ssh_config' 'projectEu' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false -h '\''127.0.0.1'\'' -u '\''test'\'' -p '\''aaaaaaaa'\'' '\''testdb'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `test1234`;USE `test1234`;'\'' && cat)' | mysql -h '127.0.0.1' -u 'root' -p 'root'";
        $this->assertEquals($result, (string)$builder);
    }

    public function testShellBuilderGroup(): void
    {
        $builder = new ShellBuilder();
        $builder->add($builder->createGroup()
            ->add(
                $builder->createCommand('echo')->addArgument('hello')
            )->and('cat'));
        $this->assertEquals("(echo 'hello' && cat)", (string)$builder);
    }

    public function testShellBuilderGroupSameShell(): void
    {
        $builder = new ShellBuilder();
        $builder->add($builder->createGroup(true)
            ->add(
                $builder->createCommand('echo')->addArgument('hello')
            )->and('cat'));
        $this->assertEquals("{echo 'hello' && cat;}", (string)$builder);
    }

    public function testSimpleSshCommand(): void
    {
        $result = "ssh -F 'php://temp' 'hosta'";
        $builder = new ShellBuilder();
        $builder->createCommand('ssh')
            ->addShortOption('F', 'php://temp')
            ->addArgument('hosta')
            ->addToBuilder();
        $this->assertEquals($result, (string)$builder);
    }

    public function testFaultyAddCommand(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        $builder = new ShellBuilder();
        $builder->add(false);
    }

    public function testFaultyPipeCommand(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        $builder = new ShellBuilder();
        $builder->add('a')->pipe(false);
    }

    public function testFaultyOrCommand(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('Provided the wrong type - only ShellCommand and ShellBuilder allowed');
        $builder = new ShellBuilder();
        $builder->add('a')->or(false);
    }

    public function testFaultyCommandChainNoBaseCommand(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('You have to first add a command before you can combine it');
        $builder = new ShellBuilder();
        $builder->pipeWithForward('a');
    }

    public function testFaultyCommandAndNoBaseCommand(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('You have to first add a command before you can combine it');
        $builder = new ShellBuilder();
        $builder->and('a');
    }

    public function testBuilderToArray(): void
    {
        $builder = new ShellBuilder();
        $builder->add('c')->pipe('a');
        $this->assertEquals('c | a', (string)$builder);
        $debug = $builder->__toArray();
        $this->assertCount(2, $debug);
        $this->assertEquals('c', $debug[0]['executable']);
    }

    public function testRedirectTo(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('echo')->addArgument('hello')->addToBuilder()
            ->redirectOutput('test.txt');
        $this->assertEquals("echo 'hello' > test.txt", (string)$builder);
    }

    public function testAsyncCommandBuilder(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('echo')->addArgument('hello')->addToBuilder()
            ->runAsynchronously();
        $this->assertEquals("coproc echo 'hello'", (string)$builder);
    }

    public function testAsyncListCommandBuilder(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('echo')->addArgument('hello')->addToBuilder()
            ->async('ls');
        $this->assertEquals("echo 'hello' & ls", (string)$builder);
    }

    public function testRedirectToAppend(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('echo')->addArgument('hello')->addToBuilder()
            ->redirectOutput('test.txt', true);
        $this->assertEquals("echo 'hello' >> test.txt", (string)$builder);
    }

    public function testRedirectToInput(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('mysql')->addArgument('database')->addToBuilder()
            ->redirectInput(
                $builder->createCommand('mysqldump')
                    ->addNoSpaceArgument('db')
                    ->addArgument('.sql', false)
            );
        $this->assertEquals("mysql 'database' < mysqldump db.sql", (string)$builder);
    }

    public function testRedirectError(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('echo')->addArgument('not-existing', false)->addToBuilder()
            ->redirectError('/var/logs/errors');
        $this->assertEquals("echo not-existing 2> /var/logs/errors", (string)$builder);
    }

    public function testRedirectBetweenFiles(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('echo')->addArgument('not-existing', false)->addToBuilder()
            ->redirect('/var/logs/errors');
        $this->assertEquals("echo not-existing >& /var/logs/errors", (string)$builder);
    }

    public function testRedirectErrorToOutput(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('echo')->addArgument('not-existing', false)->addToBuilder()
            ->redirect('/var/logs/errors')
            ->redirectErrorToOutput()
        ;
        $this->assertEquals("echo not-existing >& /var/logs/errors 2>&1", (string)$builder);
    }


    public function testRedirectBetweenFilesToRight(): void
    {
        $builder = new ShellBuilder();
        $builder->createCommand('file.txt')->addToBuilder()
            ->redirect('ls', false);
        $this->assertEquals("file.txt <& ls", (string)$builder);
    }

    public function testShellBuilderToStringEqualsShellCommandToString(): void
    {
        $builder = new ShellBuilder();
        $this->assertEquals((string)$builder->createCommand('echo')->addToBuilder(), (string)$builder->createCommand('echo'));
    }

    public function testRemoteShellCommand(): void
    {
        $result = "ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false -h '\''database'\'' -u '\''root'\'' -p '\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h '127.0.0.1' -P 2206 -u 'root' -p 'root'";
        $builder = new ShellBuilder();
        $mysqlDump = $builder->createCommand('mysqldump', true)
            ->addOption('opt')
            ->addOption('skip-comments')
            ->addOption('single-transaction')
            ->addOption('lock-tables', 'false', false, true)
            ->addShortOption('h', 'database')
            ->addShortOption('u', 'root')
            ->addShortOption('p', 'root')
            ->addArgument('sequelmovie')
            ->addToBuilder();
        $builder->createCommand('ssh')
            ->addShortOption('F', 'php://temp')
            ->addArgument('hostc')
            ->addSubCommand(
                $mysqlDump->pipe(
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
                    ->addShortOption('P', '2206', false)
                    ->addShortOption('u', 'root')
                    ->addShortOption('p', 'root')
            )
        ;
        $this->assertEquals($result, (string)$builder);
    }

    public function testRemoteShellCommandMultiplePiping(): void
    {
        $result = "ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false -h '\''database'\'' -u '\''root'\'' -p '\''root'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat) | gzip' | gunzip | mysql -h '127.0.0.1' -P 2206 -u 'root' -p 'root'";
        $builder = new ShellBuilder();
        $mysqlDump = $builder->createCommand('mysqldump', true)
            ->addOption('opt')
            ->addOption('skip-comments')
            ->addOption('single-transaction')
            ->addOption('lock-tables', 'false', false, true)
            ->addShortOption('h', 'database')
            ->addShortOption('u', 'root')
            ->addShortOption('p', 'root')
            ->addArgument('sequelmovie')
            ->addToBuilder();
        $builder->createCommand('ssh')
            ->addShortOption('F', 'php://temp')
            ->addArgument('hostc')
            ->addSubCommand(
                $mysqlDump->pipe(
                    $mysqlDump->createGroup()
                        ->createCommand('echo')
                        ->addArgument('CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;')
                        ->addToBuilder()
                        ->and('cat')
                )->pipe('gzip')
            )
            ->addToBuilder()
            ->pipe('gunzip')
            ->pipe(
                $builder->createCommand('mysql')
                    ->addShortOption('h', '127.0.0.1')
                    ->addShortOption('P', '2206', false)
                    ->addShortOption('u', 'root')
                    ->addShortOption('p', 'root')
            )
        ;
        $this->assertEquals($result, (string)$builder);
    }

    public function testRemoteShellCommandSpecialCharacter(): void
    {
        $result = "ssh -F 'php://temp' 'hostc' 'mysqldump --opt --skip-comments --single-transaction --lock-tables=false -h '\''database'\'' -u '\''root'\'' -p '\''root#password'\''\'\'''\''\"_!'\'' '\''sequelmovie'\'' | (echo '\''CREATE DATABASE IF NOT EXISTS `sequelmovie2`;USE `sequelmovie2`;'\'' && cat)' | mysql -h '127.0.0.1' -P 2206 -u 'root' -p 'root'";
        $builder = new ShellBuilder();
        $mysqlDump = $builder->createCommand('mysqldump', true)
            ->addOption('opt')
            ->addOption('skip-comments')
            ->addOption('single-transaction')
            ->addOption('lock-tables', 'false', false, true)
            ->addShortOption('h', 'database')
            ->addShortOption('u', 'root')
            ->addShortOption('p', "root#password'\"_!")
            ->addArgument('sequelmovie')
            ->addToBuilder();
        $builder->createCommand('ssh')
            ->addShortOption('F', 'php://temp')
            ->addArgument('hostc')
            ->addSubCommand(
                $mysqlDump->pipe(
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
                    ->addShortOption('P', '2206', false)
                    ->addShortOption('u', 'root')
                    ->addShortOption('p', 'root')
            )
        ;
        $this->assertEquals($result, (string)$builder);
    }

    public function testRsyncCommand(): void
    {
        $result = "rsync -vvv -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'";
        $rsync = new ShellBuilder();
        $rsync->createCommand('rsync')
            ->addShortOption('vvv')
            ->addShortOption('az')
            ->addShortOption('e')
            ->addSubCommand(
                $rsync->createCommand('ssh')
                ->addShortOption('F', 'php://temp')
            )
            ->addArgument('hosta:/var/www/prod/var/storage/')
            ->addArgument('./var/storage/')->addToBuilder();
        $this->assertEquals($result, (string)$rsync);
    }

    public function testRsyncCommandWithSubCommandAsOption(): void
    {
        $result = "rsync -vvv -az --e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'";
        $rsync = new ShellBuilder();
        $rsync->createCommand('rsync')
            ->addShortOption('vvv')
            ->addShortOption('az')
            ->addOption('e', $rsync->createCommand('ssh')
                ->addShortOption('F', 'php://temp'))
            ->addArgument('hosta:/var/www/prod/var/storage/')
            ->addArgument('./var/storage/')->addToBuilder();
        $this->assertEquals($result, (string)$rsync);
    }

    public function testRsyncCommandWithSubCommandAsShortOption(): void
    {
        $result = "rsync -vvv -az -e 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'";
        $rsync = new ShellBuilder();
        $rsync->createCommand('rsync')
            ->addShortOption('vvv')
            ->addShortOption('az')
            ->addShortOption('e', $rsync->createCommand('ssh')
                ->addShortOption('F', 'php://temp'))
            ->addArgument('hosta:/var/www/prod/var/storage/')
            ->addArgument('./var/storage/')->addToBuilder();
        $this->assertEquals($result, (string)$rsync);
    }

    public function testRsyncCommandWithSubCommandAsArgument(): void
    {
        $result = "rsync -vvv -az 'ssh -F '\''php://temp'\''' 'hosta:/var/www/prod/var/storage/' './var/storage/'";
        $rsync = new ShellBuilder();
        $rsync->createCommand('rsync')
            ->addShortOption('vvv')
            ->addShortOption('az')
            ->addArgument($rsync->createCommand('ssh')
                ->addShortOption('F', 'php://temp'))
            ->addArgument('hosta:/var/www/prod/var/storage/')
            ->addArgument('./var/storage/')->addToBuilder();
        $this->assertEquals($result, (string)$rsync);
    }

    public function testCommandProcessSubstitutionChain(): void
    {
        // this example has been taken from: https://stackoverflow.com/questions/11003039/python-execute-complex-shell-command
        $builder = ShellBuilder::new()->createCommand('diff')
            ->addArgument(
                ShellBuilder::new()->createCommand('ssh')
                    ->addShortOption('n', 'root@10.22.254.34', false)
                    ->addArgument('cat', false)
                    ->addArgument('/vms/cloudburst.qcow2.*', false)
                    ->isProcessSubstitution(),
                false
            )
            ->addArgument(
                ShellBuilder::new()->createCommand('ssh')
                    ->addShortOption('n', 'root@10.22.254.101', false)
                    ->addArgument('cat', false)
                    ->addArgument('/vms/cloudburst.qcow2', false)
                    ->isProcessSubstitution(),
                false
            );
        $result = 'diff <(ssh -n root@10.22.254.34 cat /vms/cloudburst.qcow2.*) <(ssh -n root@10.22.254.101 cat /vms/cloudburst.qcow2)';
        $this->assertEquals($result, (string)$builder);
    }

    public function testCreateCommandWithBadArgument(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('A Shell Argument has to be a valid Shell word and cannot contain e.g whitespace');
        ShellBuilder::new()->createCommand('this is not a valid command');
    }

    public function testCondition(): void
    {
        $command = ShellBuilder::new()->addCondition(ArithmeticExpression::create()->equal('a', 'b'));
        $this->assertEquals('[[ a -eq b ]]', (string)$command);
    }

    public function testChainingConditionIntoCommmand(): void
    {
        $command = ShellBuilder::new()->add('a')->and('b')->and(FileExpression::create(true)->isSocket('unix:///dev'));
        $this->assertEquals('a && b && [[ -S "unix:///dev" ]]', (string)$command);
    }

    public function testChainingConditionIntoCommmandNotEscaped(): void
    {
        $command = ShellBuilder::new()
            ->add('a')
            ->and('b')
            ->and(FileExpression::create(true)->isSocket('unix:///dev')->escapeValue(false));
        $this->assertEquals('a && b && [[ -S unix:///dev ]]', (string)$command);
    }

    public function testSimpleConditionToDebug(): void
    {
        $command = ShellBuilder::new()->addCondition(ArithmeticExpression::create(true, true)->equal('a', 'b'));
        $this->assertEquals([0 => [
            'bashBrackets' => true,
            'negate' => true,
            'compare' => 'a',
            'operator' => '-eq',
            'compareWith' => 'b'
        ]], $command->__toArray());

        $command = ShellBuilder::new()->addCondition(FileExpression::create(true)->isOlderThan('a', 'b'));
        $this->assertEquals([0 => [
            'bashBrackets' => true,
            'negate' => false,
            'compare' => 'a',
            'operator' => '-ot',
            'compareWith' => 'b'
        ]], $command->__toArray());

        $command = ShellBuilder::new()->addCondition(StringExpression::create(false, true)->equal('a', 'b'));
        $this->assertEquals([0 => [
            'bashBrackets' => true,
            'negate' => true,
            'compare' => 'a',
            'operator' => '==',
            'compareWith' => 'b'
        ]], $command->__toArray());

        $command = ShellBuilder::new()->addCondition(StringExpression::create(false, true)->eq('a', 'b'));
        $this->assertEquals([0 => [
            'bashBrackets' => false,
            'negate' => true,
            'compare' => 'a',
            'operator' => '=',
            'compareWith' => 'b'
        ]], $command->__toArray());

        $command = ShellBuilder::new()->addCondition(StringExpression::create()->eq('a', 'b'));
        $this->assertEquals([0 => [
            'bashBrackets' => true,
            'negate' => false,
            'compare' => 'a',
            'operator' => '=',
            'compareWith' => 'b'
        ]], $command->__toArray());
    }

    public function testCommandDebugWithPipeAndCondition(): void
    {
        $command = new ShellBuilder();
        $command->addCondition(ArithmeticExpression::create()->notEqual($command->createCommand('cat'), 'b'));
        $command->and('a')->pipe('grep');
        $debug = $command->__toArray();
        $this->assertCount(3, $debug);
        // checking whether it deeply arrayfies
        $this->assertEquals('cat', $debug[0]['compare']['executable']);
        $this->assertEquals('&&', $debug[1][0]);
        $this->assertEquals('|', $debug[2][0]);
    }
}
