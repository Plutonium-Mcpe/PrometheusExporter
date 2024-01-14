<?php

namespace Plutonium\PrometheusExporter\exporter;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Loop;
use Amp\Socket\Server as SocketServer;
use Plutonium\PrometheusExporter\log\PocketmineToPsrLogger;
use pocketmine\Server;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Thread;
use RuntimeException;
use Throwable;

class Exporter extends Thread {
	public ?string $crashInfo = null;
	private bool $cleanShutdown = false;
	private bool $ready = false;
	private string $response;
	private bool $marquedAsShutdown = false;

	public function __construct(
		private string $autoloaderPath,
		private string $serverIp,
		private int $serverPort,
		private ThreadSafeLogger $logger,
		private SleeperHandlerEntry $sleeperHandlerEntry
	) {
		$this->setClassLoaders([Server::getInstance()->getLoader()]);
	}

	public function shutdownHandler() : void {
		if (!$this->cleanShutdown) {
			$error = error_get_last();

			if ($error === null) {
				$this->logger->emergency('PrometheusExporter shutdown unexpectedly');
			} else {
				$this->logger->emergency('Fatal error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
				$this->setCrashInfo($error['message']);
			}
		}
	}

	protected function onRun() : void {
		try {
			gc_enable();
			ini_set('display_errors', '1');
			ini_set('display_startup_errors', '1');
			ini_set('memory_limit', '256M');

			register_shutdown_function([$this, 'shutdownHandler']);

			if ($this->autoloaderPath !== null) {
				require $this->autoloaderPath;
			}

			$this->synchronized(function () : void {
				$this->ready = true;
				$this->notify();
			});

			$exporter = $this;
			Loop::run(function () use ($exporter) {
				$sockets = [ SocketServer::listen($exporter->serverIp . ":" . $exporter->serverPort) ];

				$router = new Router();
				$router->addRoute("GET", "/", new CallableRequestHandler(function () {
					$this->logger->debug("GET /");

					return new Response(Status::OK, [ "content-type" => "text/html" ], '<html><a href="/metrics">See Metrics</a></html>');
				}));
				$router->addRoute("GET", "/metrics", new CallableRequestHandler(function () use ($exporter) {
					$this->logger->debug("GET /metrics");

					return $exporter->handleRequest();
				}));

				$server = new HttpServer($sockets, $router, new PocketmineToPsrLogger($this->logger));

				yield $server->start();

				Loop::repeat($msInterval = 50, function ($watcherId) use ($server, $exporter) {
					if ($this->marquedAsShutdown || !$exporter->isRunning()) {
						Loop::cancel($watcherId);
						yield $server->stop();
						$this->cleanShutdown = true;
					}
				});
			});
		} catch (Throwable $e) {
			$this->setCrashInfo($e->getMessage());
			$this->logger->logException($e);
		}
	}

	public function quit() : void {
		$this->marquedAsShutdown = true;
		parent::quit();
	}

	private function setCrashInfo(string $info) : void {
		$this->synchronized(function (string $info) : void {
			$this->crashInfo = $info;
			$this->notify();
		}, $info);
	}

	public function shutdown() : void {
		$this->isKilled = true;
	}

	public function startAndWait(int $options = \pmmp\thread\Thread::INHERIT_NONE) : void {
		$this->start($options);
		$this->synchronized(function () : void {
			while (!$this->ready && $this->crashInfo === null) {
				$this->wait();
			}
			if ($this->crashInfo !== null) {
				throw new RuntimeException("PrometheusExporter failed to start: $this->crashInfo");
			}
		});
	}

	public function sendResponse(string $response) : void {
		$this->synchronized(function (string $response) : void {
			$this->response = $response;
			$this->notify();
		}, $response);
	}

	public function handleRequest() {
		$this->synchronized(function () : void {
			$this->sleeperHandlerEntry->createNotifier()->wakeupSleeper();
			$this->wait();
		});

		return new Response(Status::OK, [
			"content-type" => "text/plain; charset=utf-8"
		], $this->response);
	}
}
