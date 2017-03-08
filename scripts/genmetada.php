<?php

use CleaRest\Framework;
use CleaRest\Metadata\ConsoleTool;


require_once dirname(__FILE__) . '/../src/Framework.php';
require_once Framework::guessRootFolder() . '/vendor/autoload.php';
Framework::start();

ConsoleTool::run();