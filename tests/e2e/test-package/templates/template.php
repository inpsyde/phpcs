<?php

declare(strict_types=1);

$value = (int) date('j');

$successMessage = 'Success!';
$errorMessage = 'Error!' ?>

<?php if ($value > 3) : ?>
    <?= $successMessage ?>
<?php else : ?>
    <?= $errorMessage; ?>
<?php endif;

call_user_func('strtolower', 'foo');
