<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests\Helpers;

use SydeCS\Syde\Helpers\Hooks;
use SydeCS\Syde\Tests\TestCase;

class HooksTest extends TestCase
{
    /**
     * @test
     */
    public function testHookClosure(): void
    {
        $php = <<<'PHP'
<?php

add_action /* x */ (theHookPrefix() . 'xx', static
    fn () /* add_action('x', function () {}) */ => 'find me short!';
);

add_action('x', '__return_false');

function theHookPrefix() {
    return 'x_';
}

add_action /* x */ (theHookPrefix() . 'xx', 
    static
    function /* add_action('x', function () {}) */
    () {
        return 'find me!';
    }
);

function add_action($x, $y) {
    return function () {
        return function() {
            add_action('x', 'theHookPrefix');
        };
    };
}
PHP;

        $file = $this->factoryFile($php);

        /** @var array<int, array<string, mixed>> $tokens */
        $tokens = $file->getTokens();

        $lines = [];
        foreach ($tokens as $pos => $token) {
            if (Hooks::isHookClosure($file, $pos)) {
                $lines[] = $token['line'];
            }
        }

        static::assertSame(
            [4, 15],
            $lines,
        );
    }
}
