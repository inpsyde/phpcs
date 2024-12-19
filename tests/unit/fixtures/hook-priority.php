<?php
// @phpcsSniff Syde.WordPress.HookPriority

add_action('foo', 'bar');

add_filter('foo', [ArrayObject::class, 'meh']);

add_filter(
    'foo',
    function() {
        return true;
    }
);

add_action(
    'hook',
    function() {
        echo 'hello';
    }
);

add_action('foo', 'bar', 10);
add_action('foo', [ArrayObject::class, 'meh'], -500);

add_filter('foo', 'bar', 500);
add_filter('foo', [ArrayObject::class, 'meh'], 20);

add_filter(
    'foo',
    function() {
        return true;
    },
    -500
);

add_action(
    'hook',
    function() {
        echo 'hello';
    },
    9999
);

// @phpcsWarningOnNextLine PHP_INT_MIN
add_filter('foo', 'foo', PHP_INT_MIN);
// @phpcsWarningOnNextLine PHP_INT_MIN
add_filter('foo', [ArrayObject::class, 'meh'], PHP_INT_MIN);
// @phpcsWarningOnNextLine PHP_INT_MIN
add_filter(
    'foo',
    function() {
        return true;
    },
    PHP_INT_MIN
);

// @phpcsWarningOnNextLine PHP_INT_MIN
add_action('foo', 'foo', PHP_INT_MIN);
// @phpcsWarningOnNextLine PHP_INT_MIN
add_action('foo', [ArrayObject::class, 'meh'], PHP_INT_MIN);
// @phpcsWarningOnNextLine PHP_INT_MIN
add_action(
    'hook',
    function() {
        echo 'hello';
    },
    PHP_INT_MIN
);

// @phpcsWarningOnNextLine PHP_INT_MAX
add_filter('foo', 'foo', PHP_INT_MAX);
// @phpcsWarningOnNextLine PHP_INT_MAX
add_filter('foo', [ArrayObject::class, 'meh'], PHP_INT_MAX);
// @phpcsWarningOnNextLine PHP_INT_MAX
add_filter('foo',
    function() {
        return true;
    },
    PHP_INT_MAX
);

add_action('foo', 'foo', PHP_INT_MAX);
add_action('foo', [ArrayObject::class, 'meh'], PHP_INT_MAX);
add_action(
    'foo',
    function() {
        return true;
    },
    PHP_INT_MAX
);
