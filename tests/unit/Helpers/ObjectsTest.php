<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests\Helpers;

use SydeCS\Syde\Helpers\Objects;
use SydeCS\Syde\Tests\TestCase;

class ObjectsTest extends TestCase
{
    /**
     * @test
     */
    public function testAllInterfacesFullyQualifiedNames(): void
    {
        $php = <<<'PHP'
<?php
namespace Foo;

use X\Partial as Aliased;
use Y\Full;

function () use ($test) {
    return $test;
}

class Test1 implements Bar, \X, \Y\Y, Aliased\Sub, Full
{
}

class Test2 implements Bar, \X, \Y\Y, Aliased\Sub, Full extends \Y, Z
{
}
PHP;
        $file = $this->factoryFile($php);

        $class1 = (int) $file->findNext(T_CLASS, 0);
        $class2 = (int) $file->findNext(T_CLASS, $class1 + 1);

        static::assertSame(
            ['\\Foo\\Bar', '\\X', '\\Y\\Y', '\\X\\Partial\\Sub', '\\Y\\Full'],
            Objects::allInterfacesFullyQualifiedNames($file, $class1),
        );

        static::assertSame(
            ['\\Foo\\Bar', '\\X', '\\Y\\Y', '\\X\\Partial\\Sub', '\\Y\\Full'],
            Objects::allInterfacesFullyQualifiedNames($file, $class2),
        );
    }

    /**
     * @test
     */
    public function testCountProperties(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    private readonly Test $var1;
    static private string $var2;
    public static $var3;
    static int $var4;
    var $var5;
    
    function foo($foo, int $bar) {
        $this->var1 = $bar;
        
        return new class() {
            static private $x1;
            public static $x2;
            static $x3;
            var $x4;
        };
    }
    
    static private readonly Test $var6;
    
    function foo($foo, int $bar) {
        var $x4;
    }
    
    var $var7;
}
PHP;

        $file = $this->factoryFile($php);

        static::assertSame(7, Objects::countProperties($file, (int) $file->findNext(T_CLASS, 1)));
    }
}
