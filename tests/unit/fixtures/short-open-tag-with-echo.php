<?php
// @phpcsSniff Syde.PHP.ShortOpenTagWithEcho

echo 'content';

echo 'content', 'yet another';

echo ('content'), 'yet';

echo htmlentities('content');

echo $GLOBALS['flag'] ? 'yes' : 'no'; echo 'maybe', 'no'; ?>

<?php echo 'content';
echo 'no' ?>

<?php echo 'content' // @phpcsWarningOnThisLine ?>

<?php echo 'content', 'yet another'; // @phpcsWarningOnThisLine ?>

<?php echo ('content'), 'yet'; // @phpcsWarningOnThisLine ?>

<?php echo htmlentities('content') // @phpcsWarningOnThisLine ?>

<?php echo $GLOBALS['flag'] ? 'yes' : 'no'; echo 'maybe', 'no'; // @phpcsWarningOnThisLine ?>
