<?php
// @phpcsSniff Syde.NamingConventions.VariableName

// @phpcsSniffPropertiesStart
// $ignoreProperties = true;
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

    public static $FooBar = 'FooBar';

    public $foo_bar = 'foo_bar';

    protected $fooBar = 'fooBar';

    var $foo_Bar = 'foo_Bar';
}

trait Bar {

    private static $foo = 'foo';

    public static $FooBar = 'FooBar';

    public $foo_bar = 'foo_bar';

    protected $fooBar = 'fooBar';

    var $foo_Bar = 'foo_Bar';
}
