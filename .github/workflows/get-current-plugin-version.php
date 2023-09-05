<?php

$pluginYmlPath = dirname(__FILE__, 3) . '/plugin.yml';
$yaml = yaml_parse_file($pluginYmlPath);
if ($yaml === false) {
	throw new Exception('Failed to parse plugin.yml');
}
$version = $yaml['version'];

echo $version;
