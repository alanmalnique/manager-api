<?php

return [
    'key' => getenv('JWT_SECRET') ?: '',
    'expiration' => getenv('JWT_EXPIRATION') ?: 60
];