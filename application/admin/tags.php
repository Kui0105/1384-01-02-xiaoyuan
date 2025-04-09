<?php

return [
    'menu_auth' => [
        'app\\admin\\behavior\\ClearMenuAuthCache',
    ],
    'action_begin' => [
        'app\\admin\\behavior\\SystemLog',
    ]
];