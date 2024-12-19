<?php
// @phpcsSniff Syde.NamingConventions.ElementNameMinimalLength

namespace {

    // @phpcsWarningOnNextLine
    function a()
    {

    }

    // @phpcsWarningOnNextLine
    $a = 'a';

    $db = 'db';

    class Db {

    }

    // @phpcsWarningOnNextLine
    class Ff {

    }

    // @phpcsWarningOnNextLine
    interface Ii {

    }

    interface Up {

        // @phpcsWarningOnNextLine
        public function z();

        public function id();
    }

    // @phpcsWarningOnNextLine
    trait T {

        // @phpcsWarningOnNextLine
        public function z() {

        }

        public function go() {

        }
    }

    for ($i = 1; $i < 10; $i ++) {
        echo $i;
    }

    // @phpcsWarningOnNextLine
    for ($u = 1; $u < 10; $u ++) {
        // @phpcsWarningOnNextLine
        echo $u;
    }

    // @phpcsWarningOnNextLine
    const A = 'a';

    const ID = 'ID';

    class It {

        public function hello()
        {

        }

        // @phpcsWarningOnNextLine
        public function ciao($foo, $x = 'x')
        {

        }

        // @phpcsWarningOnNextLine
        private function xx()
        {

        }
    }
}


namespace ok {}

// @phpcsWarningOnNextLine
namespace hh {}
