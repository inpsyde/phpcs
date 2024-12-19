<?php

declare(strict_types=1);

namespace SydeCS\Syde\Tests\Helpers;

use SydeCS\Syde\Helpers\FunctionReturnStatement;
use SydeCS\Syde\Tests\TestCase;

class FunctionReturnStatementTest extends TestCase
{
    /**
     * @test
     */
    public function testAllInfoForFunction(): void
    {
        $php = <<<'PHP'
<?php
class Test {
    public function countInfo($x) {
        if ($x === 'void') {
            return;
        }
        
        if ($x === 'null') {
            return null;
        }
        
        $cb =  function ($x) {
            if ($x === 'void') {
                return;
            }
            
            return new class () {
                public function count($x) {
                    if ($x === 'void') {
                        return;
                    }
                    if ($x === 'null') {
                        return null;
                    }
                    
                    return new static();
                }
            };
        };
        
        if (!$cb(1)) {
            $n = new class () {
                public function something($x) {
                    if ($x === 'void') {
                        return;
                    }
                    if ($x === 'null') {
                        return null;
                    }
                    
                    return new static();
                }
            };
            
            return $n->something(1);
        }
        
        return true;
    }
}
PHP;

        $file = $this->factoryFile($php);

        $functionPos = (int) $file->findNext(T_FUNCTION, 1);

        $info = FunctionReturnStatement::allInfo($file, $functionPos);

        static::assertSame('countInfo', $file->getDeclarationName($functionPos));
        static::assertSame(['nonEmpty' => 2, 'void' => 1, 'null' => 1, 'total' => 4], $info);
    }

    /**
     * @test
     */
    public function testAllInfoShortForClosure(): void
    {
        $php = <<<'PHP'
<?php
fn () => true;
fn () => null;
fn () => 'x';
PHP;

        $file = $this->factoryFile($php);

        $function1 = (int) $file->findNext(T_FN, 1);
        $function2 = (int) $file->findNext(T_FN, $function1 + 1);
        $function3 = (int) $file->findNext(T_FN, $function2 + 1);

        $info1 = FunctionReturnStatement::allInfo($file, $function1);
        $info2 = FunctionReturnStatement::allInfo($file, $function2);
        $info3 = FunctionReturnStatement::allInfo($file, $function3);

        static::assertSame(['nonEmpty' => 1, 'void' => 0, 'null' => 0, 'total' => 1], $info1);
        static::assertSame(['nonEmpty' => 0, 'void' => 0, 'null' => 1, 'total' => 1], $info2);
        static::assertSame(['nonEmpty' => 1, 'void' => 0, 'null' => 0, 'total' => 1], $info3);
    }
}
