<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests;

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

    public function testFaultyCommandChainNoBaseCommand(): void
    {
        $this->expectException(ShellBuilderException::class);
        $this->expectExceptionMessage('You have to first add a command before you can combine it');
        $builder = new ShellBuilder();
        $builder->pipe('a');
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
}
