<?php

namespace Plutonium\PrometheusExporter\tasks;

use const Plutonium\COMPOSER_AUTOLOADER_PATH;
use pocketmine\scheduler\AsyncTask;

class ComposerRegisterAsyncTask extends AsyncTask {

	private string $autoloaderPath;

	public function __construct() {
		$this->autoloaderPath = COMPOSER_AUTOLOADER_PATH;
	}

	public function onRun() : void {
		$prevErrorReporting = error_reporting();
		error_reporting($prevErrorReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);
		try {
			require $this->autoloaderPath;
		} finally {
			error_reporting($prevErrorReporting);
		}
	}
}
