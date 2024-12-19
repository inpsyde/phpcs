<?php
// @phpcsSniff Syde.Functions.FunctionBodyStart

namespace FunctionBodyStartTest;

function foo()
{
    return 'foo';
}

// tolerate this as PhpStorm code styler cannot distinguish between
// single line and multiline function declarations. See issue #32
function bar()
{

    return 'bar';
}

// @phpcsWarningOnNextLine WrongForSingleLineSignature
function lorem() {


    return 'ipsum';
}

// @phpcsWarningOnNextLine WrongForMultiLineDeclaration
function fooFoo(
    string $foo,
    string $bar,
    string $baz
) {
    return 'foo';
}

// @phpcsWarningOnNextLine WrongForSingleLineSignature
function fooFooBar(string $foo, string $bar, string $baz) {
    return 'foo';
}

function fooFooBar2(string $foo, string $bar, string $baz) {
    // it's ok
    return 'foo';
}

// @phpcsWarningOnNextLine WrongForMultiLineDeclaration
function barBar(
    string $foo,
    string $bar,
    string $baz
): string {
    return 'foo';
}

function barBarBaz(
    string $foo,
    string $bar,
    string $baz
): string {

    return 'foo';
}

interface Foo
{

    public function fooBar();
}

class BarBarBar
{

    public function foo()
    {
        return 'foo';
    }

    private function bar()
    {

        return 'bar';
    }

    // @phpcsWarningOnNextLine WrongForSingleLineSignature
    public function lorem() {


        return 'ipsum';
    }

    // @phpcsWarningOnNextLine WrongForMultiLineDeclaration
    protected function fooFoo(
        string $foo,
        string $bar,
        string $baz
    ) {
        return 'foo';
    }

    // @phpcsWarningOnNextLine WrongForMultiLineDeclaration
    public function barBar(
        string $foo,
        string $bar,
        string $baz
    ): string {
        return 'foo';
    }

    private function barBarBaz(
        string $foo,
        string $bar,
        string $baz
    ): string {

        return 'foo';
    }

    private function barBarBazComment(
        string $foo,
        string $bar,
        string $baz
    ): string {
        // it's ok
        return 'foo';
    }
}
