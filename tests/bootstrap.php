<?php

$autoloaderPath = dirname(__DIR__) . '/vendor/autoload.php';

if ($autoloaderPath) {
	require_once $autoloaderPath;
} else {
	throw new \InvalidArgumentException(
		sprintf("Cannot load autoloader file located at %s", $autoloaderPath)
	);
}
