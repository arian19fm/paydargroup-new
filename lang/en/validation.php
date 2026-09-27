<?php

return [
    'custom' => [
        'slug' => [
            'format' => 'The slug may only contain lowercase letters, digits and hyphens.',
            'reserved' => 'This slug is reserved and cannot be used.',
        ],
        'redirect' => [
            'protected' => 'This path belongs to the application and cannot be redirected.',
            'root' => 'The home page cannot be redirected.',
            'loop' => 'This redirect would create a loop.',
        ],
        'menu_item' => [
            'target' => 'Exactly one of “page” or “URL” must be set (unless the item has an automatic submenu).',
            'cycle' => 'An item cannot be nested under itself.',
        ],
    ],
];
