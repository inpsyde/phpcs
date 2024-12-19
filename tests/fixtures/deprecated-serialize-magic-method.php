<?php
// @phpcsSniff Syde.Classes.DeprecatedSerializeMagicMethod

class Foo {

    public function __serialize(): array
    {
        return [];
    }

    public function serialize(): array
    {
        return [];
    }

    // @phpcsErrorOnNextLine Found
    public function __sleep(): array
    {
        return [];
    }

    public function sleep(): array
    {
        return [];
    }

    // @phpcsErrorOnNextLine Found
    public function __wakeup()
    {
        return [];
    }

    public function wakeup(): array
    {
        return [];
    }

    public function __unserialize($data)
    {
        return [];
    }

    public function unserialize(): array
    {
        return [];
    }
}
