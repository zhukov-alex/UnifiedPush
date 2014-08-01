<?php

$autoloaderPath = __DIR__ . '/../autoload.php';

if ($autoloaderPath) {
	require_once $autoloaderPath;
} else {
	throw new \InvalidArgumentException(
		sprintf("Cannot load autoloader file located at %s", $autoloaderPath)
	);
}
