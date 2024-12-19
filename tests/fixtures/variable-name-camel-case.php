<?php
// @phpcsSniff Syde.NamingConventions.VariableName

// @phpcsSniffPropertiesStart
// $checkType = 'camelCase';
// @phpcsSniffPropertiesEnd

// @phpcsWarningOnNextLine SnakeCaseVar
$foo_bar = 'foo_bar';

$foo = 'foo';

$fooBar = 'fooBar';

// @phpcsWarningOnNextLine SnakeCaseVar
$foo_Bar = 'foo_Bar';

// @phpcsWarningOnNextLine SnakeCaseVar
$FooBar = 'foo_Bar';

global $is_edge;
$is_edge = false;

class Foo {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine SnakeCaseVar
    public static $FooBar = 'FooBar';

    // @phpcsWarningOnNextLine SnakeCaseVar
    public $foo_bar = 'foo_bar';

    protected $fooBar = 'fooBar';

    // @phpcsWarningOnNextLine SnakeCaseVar
    var $foo_Bar = 'foo_Bar';
}

trait Bar {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine SnakeCaseVar
    public static $FooBar = 'FooBar';

    // @phpcsWarningOnNextLine SnakeCaseVar
    public $foo_bar = 'foo_bar';

    protected $fooBar = 'fooBar';

    // @phpcsWarningOnNextLine SnakeCaseVar
    var $foo_Bar = 'foo_Bar';
}
