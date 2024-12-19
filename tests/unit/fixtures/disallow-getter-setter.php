<?php
// @phpcsSniff Syde.Classes.DisallowGetterSetter

function getting() {

}

function setting() {

}

interface WithAccessorsInterface {

    // @phpcsWarningOnNextLine GetterFound
    function getTheThing();

    // @phpcsWarningOnNextLine SetterFound
    function setTheThing($foo, $bar);

    function setting();
}

class WithAccessors implements \IteratorAggregate {

    function thing() {

    }

    function setting() {

    }

    // @phpcsWarningOnNextLine GetterFound
    function getTheThing() {

    }

    function withThing() {

    }

    private function setTheThing($foo, $bar) {

    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator();
    }
}
