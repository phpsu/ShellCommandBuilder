<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Tests\Collection;

use PHPSu\ShellCommandBuilder\Collection\CollectionTuple;
use PHPSu\ShellCommandBuilder\Definition\ControlOperator;
use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;
use PHPSu\ShellCommandBuilder\ShellBuilder;
use PHPUnit\Framework\TestCase;

final class CollectionTupleTest extends TestCase
{
    public function testCollectionTuple(): void
    {
        $tuple = CollectionTuple::create('echo "hello world"', ControlOperator::AND_OPERATOR);
        $this->assertEquals(' && echo "hello world"', (string)$tuple);
    }

    public function testCollectionWithBuilderTuple(): void
    {
        $builder = new ShellBuilder();
        $builder->add($builder->createCommand('a'));
        $tuple = CollectionTuple::create($builder, ControlOperator::AND_OPERATOR);
        $this->assertEquals(' && a', (string)$tuple);
    }

    public function testCollectionWithCommandTuple(): void
    {
        $tuple = CollectionTuple::create((new ShellBuilder())->createCommand('a'), ControlOperator::OR_OPERATOR);
        $this->assertEquals(' || a', (string)$tuple);
    }

    public function testWithWrongType(): void
    {
        $this->expectException(ShellBuilderException::class);
        CollectionTuple::create(3892740, ControlOperator::OR_OPERATOR);
    }

    public function testTupleToArray(): void
    {
        $tuple = CollectionTuple::create('a', ControlOperator::OR_OPERATOR);
        $this->assertEquals(['||', 'a'], $tuple->__toArray());
    }
}
