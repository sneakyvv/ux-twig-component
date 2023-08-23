<?php

namespace Symfony\UX\TwigComponent\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\UX\TwigComponent\Hierarchy;

/**
 * @author Bart Vanderstukken <bart.vanderstukken@gmail.com>
 */
final class HierarchyTest extends TestCase
{
    public function testCanProxyGetters(): void
    {
        $hierarchy = new Hierarchy(new class() {
            private int $count = 0;

            public function getCount(): int
            {
                return ++$this->count;
            }
        });

        $this->assertSame(1, $hierarchy->getCount());
        $this->assertSame(2, $hierarchy->count());
    }

    public function testCanProxyIssers(): void
    {
        $hierarchy = new Hierarchy(new class() {
            private int $count = 0;

            public function isCount(): int
            {
                return ++$this->count;
            }
        });

        $this->assertSame(1, $hierarchy->isCount());
        $this->assertSame(2, $hierarchy->count());
    }

    public function testCanProxyHassers(): void
    {
        $hierarchy = new Hierarchy(new class() {
            private int $count = 0;

            public function hasCount(): int
            {
                return ++$this->count;
            }
        });

        $this->assertSame(1, $hierarchy->hasCount());
        $this->assertSame(2, $hierarchy->count());
    }

    public function testCanProxyGettersWithArguments(): void
    {
        $hierarchy = new Hierarchy(new class() {
            private int $count = 0;

            public function getCount(int $step, int $jump = 0): int
            {
                return $this->count += $step + $jump;
            }
        });

        $this->assertSame(5, $hierarchy->getCount(5));
        $this->assertSame(20, $hierarchy->count(5, 10));
    }

    public function testCanProxyPublicProperties(): void
    {
        $hierarchy = new Hierarchy(new class() {
            public $foo = 'bar';
        });

        $this->assertSame('bar', $hierarchy->foo());
    }

    public function testCanProxyArrayAccess(): void
    {
        $hierarchy = new Hierarchy(new class() implements \ArrayAccess {
            private $array = ['foo' => 'bar'];

            public function offsetExists(mixed $offset): bool
            {
                return isset($this->array[$offset]);
            }

            public function offsetGet(mixed $offset): mixed
            {
                return $this->array[$offset];
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
            }

            public function offsetUnset(mixed $offset): void
            {
            }
        });

        $this->assertSame('bar', $hierarchy->foo());
    }

    public function testCannotProxyMethodsThatDoNotExist(): void
    {
        $hierarchy = new Hierarchy(new class() {});

        $this->expectException(\InvalidArgumentException::class);

        $hierarchy->getSomething();
    }

    public function testCanProxyMethodAndPropertiesOfParentObjects(): void
    {
        $hierarchy = new Hierarchy(new class() {
            public $bar = 'bar';
            private int $count = 0;

            public function getCount(): int
            {
                return ++$this->count;
            }
        });
        $hierarchy = $hierarchy->add(new class() {
            public $foo = 'foo';
        });

        $this->assertSame('foo', $hierarchy->foo());
        $this->assertSame('bar', $hierarchy->parent->bar());
        $this->assertSame(1, $hierarchy->parent->count());
    }
}
