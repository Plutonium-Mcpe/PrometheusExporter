<?php

namespace Plutonium\PrometheusExporter\log;

use Logger;
use LogLevel;
use Psr\Log\LoggerInterface;
use Stringable;

class PocketmineToPsrLogger implements LoggerInterface {
	public function __construct(
		private Logger $delegate
	) {
	}

	public function emergency(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::EMERGENCY, $message, $context);
	}

	public function alert(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::ALERT, $message, $context);
	}

	public function critical(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::CRITICAL, $message, $context);
	}

	public function error(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::ERROR, $message, $context);
	}

	public function warning(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::WARNING, $message, $context);
	}

	public function notice(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::NOTICE, $message, $context);
	}

	public function info(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::INFO, $message, $context);
	}

	public function debug(Stringable|string $message, array $context = []) : void {
		$this->log(LogLevel::DEBUG, $message, $context);
	}

	public function log($level, Stringable|string $message, array $context = []) : void {
		$this->delegate->log($level, sprintf("%s (%s)", $message, json_encode($context)));
	}
}
