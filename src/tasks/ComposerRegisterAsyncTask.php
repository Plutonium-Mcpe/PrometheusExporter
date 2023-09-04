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
		require $this->autoloaderPath;
	}
}
