<?php

declare(strict_types=1);

namespace JavaGen;

use JavaGen\helper\GeneratorNames;
use JavaGen\tasks\ProcessGenerationDataTask;
use pocketmine\event\Listener;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\Server;

class EventListener implements Listener {

	public function onChunkGenerate(ChunkPopulateEvent $event): void {
		if (!GeneratorNames::isOneOf($event->getWorld()->getProvider()->getWorldData()->getGenerator())) {
			return;
		}

		$asyncPool = Server::getInstance()->getAsyncPool();
		foreach ($asyncPool->getRunningWorkers() as $i) {
			$asyncPool->submitTaskToWorker(new ProcessGenerationDataTask(), $i);
		}
	}
}