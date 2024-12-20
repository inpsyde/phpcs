<?php
// @phpcsSniff Syde.PHP.DisallowShortOpenTag

// @phpcsProcessFixtureStart
function (string $sniff, array $messages, array $warnings, array $errors, array $properties)
{
    // If `short_open_tag` ini set is false, we can only detect *possible* use of short open tags
    // and those will be reported as warnings, not errors as per tokens below.
    // So we swap error with warnings.
    if (!filter_var(ini_get('short_open_tag'), FILTER_VALIDATE_BOOLEAN)) {
        return [$sniff, $messages, $errors, $warnings, $properties];
    }

    return [$sniff, $messages, $warnings, $errors, $properties];
}
// @phpcsProcessFixtureEnd
?>
<div>
    <?= strtolower($x) ?>
</div>
<?php if (false): ?>
    Hi there!
<?php endif ?>
<? // @phpcsErrorOnThisLine PossibleFound
echo 'H!'
?>
<?php if (false): ?>
    Hi there!
<?php endif ?>
<? echo 'H!' // @phpcsErrorOnThisLine PossibleFound ?>
<div>
    <? echo strtolower($x) // @phpcsErrorOnThisLine PossibleFound ?>
</div>
