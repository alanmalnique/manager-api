<?php

use Aeatech\Router\Response\Response;

require_once(__DIR__.'./../vendor/autoload.php');
require_once(__DIR__.'./../env.php');
require_once(__DIR__.'./../env.override.php');

\App\Provider\KernelProvider::boot();