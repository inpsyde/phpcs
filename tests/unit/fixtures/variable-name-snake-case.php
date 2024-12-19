<?php
// @phpcsSniff Syde.NamingConventions.VariableName

// @phpcsSniffPropertiesStart
// $checkType = 'snake_case';
// $ignoredNames = ['IAMALLOWED', 'anId'];
// @phpcsSniffPropertiesEnd

$foo_bar = 'foo_bar';

$foo = 'foo';

// @phpcsWarningOnNextLine CamelCaseVar
$fooBar = 'fooBar';

// @phpcsWarningOnNextLine CamelCaseVar
$foo_Bar = 'foo_Bar';

// @phpcsWarningOnNextLine CamelCaseVar
$FooBar = 'foo_Bar';

global $is_NS4;
$is_NS4 = false;

$_GET = [];

$IAMALLOWED = true;

class Foo {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine CamelCaseVar
    public static $FooBar = 'FooBar';

    public $foo_bar = 'foo_bar';

    // @phpcsWarningOnNextLine CamelCaseVar
    protected $fooBar = 'fooBar';

    private $anId = 1;

    // @phpcsWarningOnNextLine CamelCaseVar
    var $foo_Bar = 'foo_Bar';
}

trait Bar {

    private static $foo = 'foo';

    // @phpcsWarningOnNextLine CamelCaseVar
    public static $FooBar = 'FooBar';

    public $foo_bar = 'foo_bar';

    // @phpcsWarningOnNextLine CamelCaseVar
    protected $fooBar = 'fooBar';

    // @phpcsWarningOnNextLine CamelCaseVar
    var $foo_Bar = 'foo_Bar';
}
