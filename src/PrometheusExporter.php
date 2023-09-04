<?php

namespace Plutonium\PrometheusExporter;

use const Plutonium\COMPOSER_AUTOLOADER_PATH;
use Plutonium\PrometheusExporter\Exporter\ExporterInterface;
use Plutonium\PrometheusExporter\metrics\MetricsManager;
use Plutonium\PrometheusExporter\tasks\ComposerRegisterAsyncTask;
use Plutonium\PrometheusExporter\timings\Timing;
use function pocketmine\critical_error;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\plugin\ResourceProvider;
use pocketmine\Server;

require_once __DIR__ . '/CoreConstants.php';

class PrometheusExporter extends PluginBase {
	private static PrometheusExporter $instance;
	private ExporterInterface $exporter;
	private MetricsManager $metricsManager;

	public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, string $file, ResourceProvider $resourceProvider) {
		self::$instance = $this;
		parent::__construct($loader, $server, $description, $dataFolder, $file, $resourceProvider);
	}

	public static function getInstance() : PrometheusExporter {
		return self::$instance;
	}

	protected function onLoad() : void {
		date_default_timezone_set('UTC');

		$this->saveDefaultConfig();

		if (is_file(COMPOSER_AUTOLOADER_PATH)) {
			require_once(COMPOSER_AUTOLOADER_PATH);

			$asyncPool = $this->getServer()->getAsyncPool();
			$asyncPool->addWorkerStartHook(function (int $workerId) use ($asyncPool) : void {
				$asyncPool->submitTaskToWorker(new ComposerRegisterAsyncTask(), $workerId);
			});
		} else {
			critical_error("Composer autoloader not found at " . COMPOSER_AUTOLOADER_PATH);
			critical_error("Please install/update Composer dependencies or use provided builds.");

			$this->getServer()->shutdown();
		}

		Timing::init();
		$this->exporter = new ExporterInterface($this);
		$this->metricsManager = new MetricsManager($this);
	}

	protected function onEnable() : void {
		$this->exporter->start();
	}

	public function getExporter() : ExporterInterface {
		return $this->exporter;
	}

	public function getMetricsManager() : MetricsManager {
		return $this->metricsManager;
	}
}
