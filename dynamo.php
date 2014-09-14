<?php
date_default_timezone_set('UTC');
require dirname(__FILE__) . '/dynamo-lib/brickyard.php';
$by = new brickyard;
$by->init();
$by->inDevelMode = true;
$by->throwTheseErrors = E_ALL ^ (E_WARNING | E_NOTICE); // nojo, php

$app = new dynamo();
$app->framework = $by;
$app->run($argv, getcwd());