<?php

namespace Plutonium\PrometheusExporter;

// composer autoload doesn't use require_once and also pthreads can inherit things
if (\defined('Plutonium\_CORE_CONSTANTS_INCLUDED')) {
	return;
}
\define('Plutonium\_CORE_CONSTANTS_INCLUDED', true);

\define('Plutonium\COMPOSER_AUTOLOADER_PATH', \dirname(__FILE__, 2) . '/vendor/autoload.php');
