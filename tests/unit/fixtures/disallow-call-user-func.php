<?php
// @phpcsSniff Syde.Functions.DisallowCallUserFunc

function test() {
    // @phpcsWarningOnNextLine call_user_func_call_user_func
    return call_user_func('strtolower', 'foo');
}

echo 'call_user_func_array';

$foo = [
    'call_user_func',
    'call_user_func_array',
];

class Foo {

    private function test() {
        // @phpcsWarningOnNextLine call_user_func_call_user_func_array
        return call_user_func_array('strtolower', ['foo']);
    }
}
