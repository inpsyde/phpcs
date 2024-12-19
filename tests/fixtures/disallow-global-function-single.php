<?php
// @phpcsSniff Syde.Functions.DisallowGlobalFunction

class Foo
{
    function test() {

    }
}

// @phpcsErrorOnNextLine
function test() {

}
