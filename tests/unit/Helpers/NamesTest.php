<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests\Helpers;

use SydeCS\Syde\Helpers\Names;
use SydeCS\Syde\Tests\TestCase;

class NamesTest extends TestCase
{
    /**
     * @test
     */
    public function testNameableTokenName(): void
    {
        $php = <<<'PHP'
<?php
namespace {

    interface a {
        public static function b(): string;
    }
    function c(string $d): string {
        return '';
    }
    abstract class e implements a {
        const f = 'f';
        function g(): string {
            return c($h = 'h');
        }
    }
    trait i {
        public function j() {
        }
    }
    class k {
        use i;
        function l() {
        }
    }
    enum m {
        case n;
        case o;
    }
    enum p {
        case q = 'q';
        case r = 'r';
    }
    ((new k())->l());
    $s = 's';
}

namespace t {

}
PHP;
        $file = $this->factoryFile($php);

        /** @var list<string> $names */
        $names = [];
        foreach ($file->getTokens() as $pos => $token) {
            $name = (string) Names::nameableTokenName($file, (int) $pos);
            if ($name !== '') {
                $names[] = $name;
            }
        }

        static::assertSame(range('a', 't'), $names);
    }
}
