<?php
// @phpcsSniff Syde.WordPress.HookClosureReturn

add_action('foo', 'bar');

add_filter('foo', [ArrayObject::class, 'meh']);

apply_filters('foo', function() {
    echo 'hello';
});

$meh = function() {
    return 'meh';
};

add_action(
    'hook',
    function() {
        // @phpcsErrorOnPreviousLine ReturnFromAction
        return 'meh';
    }
);

add_action(
    'hook',
    function() {
        return;
    }
);

add_action(
    'hook',
    function() {
        echo 'Hello';
    }
);

add_filter(
    'hook',
    function() {
        return 'meh';
    }
);

add_filter(
    'hook',
    function() {
        // @phpcsErrorOnPreviousLine NoReturnFromFilter
        return;
    }
);

add_filter(
    'hook',
    function() {
        // @phpcsErrorOnPreviousLine NoReturnFromFilter
        echo 'Hello';
    }
);

function foo_bar_baz(): bool {

    add_filter(
        'hook',
        function() {
            // @phpcsErrorOnPreviousLine NoReturnFromFilter
            echo 'Hello';
        }
    );

    add_filter(
        'hook',
        function () {
            return null;
        }
    );

    return true;
}

class TestMe {

    public function testMeMethodAction()
    {
        add_action(
            'hook',
            function() {
                // @phpcsErrorOnPreviousLine ReturnFromAction
                return 'meh';
            }
        );
    }

    public function testMeMethodFilter(): bool
    {
        add_filter(
            'hook',
            function() {
                // @phpcsErrorOnPreviousLine NoReturnFromFilter
                return;
            }
        );

        return true;
    }

    public function testMeMethodOk()
    {
        add_filter(
            'hook',
            function() {
                return 'meh';
            }
        );
    }
}
