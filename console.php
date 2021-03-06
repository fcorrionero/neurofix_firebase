#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application('Neurofix App','v1.0.0');
$app->add(new \App\Console\CreateUser());
$app->add(new \App\Console\GetQuest());
$app->add(new \App\Console\InitUser());
$app->add(new \App\Console\Report());
//$app->add(new \App\Console\GenerateQuest());
$app -> run();
