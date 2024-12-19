<?php

declare(strict_types=1);

$value = (int) date('j');

$successMessage = 'Success!';
$errorMessage = 'Error!';

if ($value > 3) {
    echo esc_html($successMessage);
} else {
    echo $errorMessage;
}
