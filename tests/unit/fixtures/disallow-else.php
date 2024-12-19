<?php
// @phpcsSniff Syde.ControlStructures.DisallowElse

if ('x') {

} elseif ('y') {

} else {
    // @phpcsWarningOnPreviousLine
    die();
}

function test()
{
    if ('x') {

    } elseif ('y') {

    } else {
        // @phpcsWarningOnPreviousLine
        die();
    }
}

class FooBarBaz
{
    public function test()
    {
        if ('x') {

        } elseif ('y') {

        } else {
            // @phpcsWarningOnPreviousLine
            die();
        }
    }
}
