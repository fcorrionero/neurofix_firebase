#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application('Neurofix App','v1.0.0');
$app->add(new \App\Console\CreateUser());
$app -> run();