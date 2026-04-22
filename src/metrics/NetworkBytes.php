<?php

namespace Plutonium\PrometheusExporter\metrics;

use Prometheus\RegistryInterface;

class NetworkBytes extends Metric {
	public function getName() : string {
		return "network_bytes";
	}

	public function collect(RegistryInterface $registry) : void {
		$rxGauge = $registry->getOrRegisterGauge(Metric::PREFIX, "network_bytes_received_total", "Total bytes received since boot", ["interface"]);
		$txGauge = $registry->getOrRegisterGauge(Metric::PREFIX, "network_bytes_sent_total", "Total bytes sent since boot", ["interface"]);

		$raw = @file_get_contents('/proc/net/dev');
		if ($raw === false) {
			return;
		}

		foreach (explode("\n", $raw) as $line) {
			$line = trim($line);
			if ($line === '' || !str_contains($line, ':')) {
				continue;
			}

			[$iface, $stats] = explode(':', $line, 2);
			$iface = trim($iface);

			if ($iface === 'lo') {
				continue;
			}

			$parts = preg_split('/\s+/', trim($stats));
			$rxGauge->set((float) ($parts[0] ?? 0), [$iface]);
			$txGauge->set((float) ($parts[8] ?? 0), [$iface]);
		}
	}
}
