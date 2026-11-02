<?php

namespace Plutonium\PrometheusExporter\metrics;

use Closure;
use Plutonium\PrometheusExporter\PrometheusExporter;
use Plutonium\PrometheusExporter\timings\Timing;
use Prometheus\CollectorRegistry;
use Prometheus\RegistryInterface;
use Prometheus\RendererInterface;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;
use Throwable;

class MetricsManager {
	private RendererInterface $renderer;
	private RegistryInterface $registry;

	/** @var Metric[] */
	private static array $metrics = [];

	public function __construct(
		private PrometheusExporter $plugin
	) {
		$this->renderer = new RenderTextFormat();
		$this->registry = new CollectorRegistry(new InMemory());
		self::init();
	}

	private static function init() : void {
		/** @var class-string<Metric>[] $metrics */
		$metrics = [
			MemoryHeapBytes::class,
			MemoryMainThreadBytes::class,
			PlayerMaxCount::class,
			PlayerOnlineCount::class,
			PlayerPing::class,
			ThreadCount::class,
			TicksPerSecond::class,
			TickUsage::class,
			UptimeSeconds::class,
			ViewDistance::class,
			WorldChunkLoaded::class,
			WorldEntityCount::class,
			WorldLoaded::class,
			WorldPlayerCount::class,
			WorldTickRate::class,
			NetworkBytes::class,
			ChunkGenerationCount::class,
		];

		foreach ($metrics as $metric) {
			self::registerMetric(new $metric());
		}
	}

	public static function registerMetric(Metric $metric) : Metric {
		$plugin = PrometheusExporter::getInstance();
		$name = $metric->getName();
		if (isset(self::$metrics[$name])) {
			$plugin->getLogger()->warning("Metric '" . $name . "' is already registered by " . self::$metrics[$name]::class . ", replacing it with " . $metric::class . ".");
		}
		self::$metrics[$name] = $metric;
		$plugin->getLogger()->debug("Metric '" . $name . "' successfully registered.");
		$metric->onRegister($plugin);

		return $metric;
	}

	private function collect(RegistryInterface $snapshotRegistry) : void {
		Timing::$collectResultTimer->time(function () use ($snapshotRegistry) {
			foreach (self::$metrics as $metric) {
				$registry = $metric->isSnapshot() ? $snapshotRegistry : $this->registry;
				Timing::getMetricsCollecterTimings($metric)->time(function () use ($metric, $registry) {
					$this->runSafely($metric, fn () => $metric->collect($registry));
				});
			}

			foreach (self::$metrics as $metric) {
				Timing::getMetricsPostCollectCollecterTimings($metric)->time(function () use ($metric) {
					$this->runSafely($metric, fn () => $metric->postCollect());
				});
			}
		});
	}

	/**
	 * @param Closure() : void $closure
	 */
	private function runSafely(Metric $metric, Closure $closure) : void {
		try {
			$closure();
		} catch (Throwable $e) {
			$this->plugin->getLogger()->error("Metric '" . $metric->getName() . "' failed to collect: " . $e->getMessage());
			$this->plugin->getLogger()->logException($e);
		}
	}

	public function render() : string {
		$snapshotRegistry = new CollectorRegistry(new InMemory(), false);
		$this->collect($snapshotRegistry);

		return Timing::$renderResultTimer->time(function () use ($snapshotRegistry) {
			return $this->renderer->render([
				...$this->registry->getMetricFamilySamples(),
				...$snapshotRegistry->getMetricFamilySamples(),
			]);
		});
	}
}
