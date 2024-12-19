<?php

declare(strict_types=1);

$value = (int) date('j');

$successMessage = 'Success!';
$errorMessage = 'Error!';

if ($value > 3) {
    echo $successMessage;
} else {
    echo $errorMessage;
}
