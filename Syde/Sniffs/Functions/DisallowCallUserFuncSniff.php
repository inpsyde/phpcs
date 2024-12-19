<?php

declare(strict_types=1);

namespace SydeCS\Syde\Sniffs\Functions;

use WordPressCS\WordPress\AbstractFunctionRestrictionsSniff;

final class DisallowCallUserFuncSniff extends AbstractFunctionRestrictionsSniff
{
    /**
     * @return array<string, array<string, string|array<string>>>
     */
    public function getGroups(): array
    {
        $message = 'The "%s" function is discouraged; directly call the variable function instead';

        return [
            'call_user_func' => [
                'type' => 'warning',
                'message' => $message,
                'functions' => [
                    'call_user_func',
                    'call_user_func_array',
                ],
            ],
        ];
    }
}
