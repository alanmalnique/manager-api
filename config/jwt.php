<?php

return [
    'key' => getenv('JWT_SECRET') ?: '',
    'expiration' => 60
];