<?php

namespace Plutonium\PrometheusExporter\metrics;

use Plutonium\PrometheusExporter\PrometheusExporter;
use Plutonium\PrometheusExporter\timings\Timing;
use Prometheus\CollectorRegistry;
use Prometheus\RegistryInterface;
use Prometheus\RendererInterface;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\InMemory;

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
			PlayerOnlineCount::class,
			ThreadCount::class,
			TicksPerSecond::class,
			TickUsage::class,
			ViewDistance::class,
			WorldChunkLoaded::class,
			WorldEntityCount::class,
			WorldLoaded::class,
			WorldPlayerCount::class,
			WorldTickRate::class
		];

		foreach ($metrics as $metric) {
			self::registerMetric(new $metric());
		}
	}

	public static function registerMetric(Metric $metric) : Metric {
		self::$metrics[$metric->getName()] = $metric;
		PrometheusExporter::getInstance()->getLogger()->debug("Metric '" . $metric->getName() . "' successfully registered.");

		return $metric;
	}

	private function collect() : void {
		Timing::$collectResultTimer->time(function () {
			foreach (self::$metrics as $metric) {
				Timing::getMetricsCollecterTimings($metric)->time(function () use ($metric) {
					$metric->collect($this->registry);
				});
			}

			foreach (self::$metrics as $metric) {
				Timing::getMetricsPostCollectCollecterTimings($metric)->time(function () use ($metric) {
					$metric->postCollect();
				});
			}
		});
	}

	public function render() : string {
		$this->collect();

		return Timing::$renderResultTimer->time(function () {
			return $this->renderer->render($this->registry->getMetricFamilySamples());
		});
	}
}
