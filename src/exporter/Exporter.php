<?php

namespace Plutonium\PrometheusExporter\exporter;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Loop;
use Amp\Socket\Server as SocketServer;
use Generator;
use Plutonium\PrometheusExporter\log\PocketmineToPsrLogger;
use pocketmine\Server;
use pocketmine\thread\log\ThreadSafeLogger;
use pocketmine\thread\Thread;
use Prometheus\RenderTextFormat;
use RuntimeException;

class Exporter extends Thread {
	private bool $ready = false;
	private string $snapshot = "";

	public function __construct(
		private string $autoloaderPath,
		private string $serverIp,
		private int $serverPort,
		private ThreadSafeLogger $logger
	) {
		$this->setClassLoaders([Server::getInstance()->getLoader()]);
	}

	protected function onRun() : void {
		gc_enable();
		ini_set('memory_limit', '256M');

		$prevErrorReporting = error_reporting();
		error_reporting($prevErrorReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);
		try {
			require $this->autoloaderPath;
		} finally {
			error_reporting($prevErrorReporting);
		}

		Loop::run(function () : Generator {
			$router = new Router();
			$router->addRoute("GET", "/", new CallableRequestHandler(function () : Response {
				$this->logger->debug("GET /");

				return new Response(Status::OK, [ "content-type" => "text/html" ], '<html><a href="/metrics">See Metrics</a></html>');
			}));
			$router->addRoute("GET", "/metrics", new CallableRequestHandler(function () : Response {
				$this->logger->debug("GET /metrics");

				return new Response(Status::OK, [ "content-type" => RenderTextFormat::MIME_TYPE ], $this->snapshot);
			}));

			$sockets = [ SocketServer::listen($this->serverIp . ":" . $this->serverPort) ];
			$server = new HttpServer($sockets, $router, new PocketmineToPsrLogger($this->logger));

			yield $server->start();

			$this->synchronized(function () : void {
				$this->ready = true;
				$this->notify();
			});

			Loop::repeat(50, function (string $watcherId) use ($server) : Generator {
				if ($this->isKilled) {
					Loop::cancel($watcherId);
					yield $server->stop();
					Loop::stop();
				}
			});
		});
	}

	public function startAndWait() : void {
		$this->start();
		$this->synchronized(function () : void {
			while (!$this->ready && !$this->isTerminated()) {
				$this->wait(100_000);
			}
		});

		if (!$this->ready) {
			$crashInfo = $this->getCrashInfo();
			throw new RuntimeException("PrometheusExporter failed to start: " . ($crashInfo?->getMessage() ?? "unknown error"));
		}
	}

	public function setSnapshot(string $snapshot) : void {
		$this->snapshot = $snapshot;
	}
}
