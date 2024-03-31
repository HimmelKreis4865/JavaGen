<?php

declare(strict_types=1);

namespace JavaGen;

use JavaGen\event\StructureGenerateEvent;
use JavaGen\generator\BaseJavaGenerator;
use JavaGen\helper\Dimension;
use JavaGen\helper\GeneratorNames;
use JavaGen\structure\Structure;
use JavaGen\structure\StructureManager;
use JavaGen\structure\StructureType;
use JavaGen\tile\JavaTile;
use JavaGen\tile\JavaTileMappings;
use pocketmine\event\Listener;
use pocketmine\event\world\ChunkPopulateEvent;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\AsyncWorker;
use pocketmine\Server;
use pocketmine\thread\Thread;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class EventListener implements Listener {

	public function onChunkGenerate(ChunkPopulateEvent $event): void {
		if (!GeneratorNames::isOneOf($event->getWorld()->getProvider()->getWorldData()->getGenerator())) {
			return;
		}

		$asyncPool = Server::getInstance()->getAsyncPool();
		foreach ($asyncPool->getRunningWorkers() as $i) {
			$asyncPool->submitTaskToWorker(new class extends AsyncTask {

				public function onRun(): void {
					$worker = Thread::getCurrentThread();
					if ($worker instanceof AsyncWorker) {
						$data = $worker->getFromThreadStore(BaseJavaGenerator::WORKER_DATA);
						if ($data !== null) {
							$this->setResult($data);
							$worker->removeFromThreadStore(BaseJavaGenerator::WORKER_DATA);
						}
					}
				}

				public function onCompletion(): void {
					if ($this->hasResult()) {
						$resultStack = json_decode($this->getResult(), true, flags: JSON_THROW_ON_ERROR);

						foreach ($resultStack as $result) {
							$expectedDimension = $result["dimension"];
							$chunkX = $result["chunkX"];
							$chunkZ = $result["chunkZ"];

							$world = Dimension::tryFrom($expectedDimension)?->findWorld();
							if ($world === null) {
								return;
							}
							$chunk = $world->getChunk($chunkX, $chunkZ);
							foreach ($result["tiles"] ?? [] as $tile) {
								$javaTile = new JavaTile($tile);
								$mapping = JavaTileMappings::getInstance()->findMapping($javaTile->getId());
								if ($mapping !== null) {
									$mapping($javaTile, $world, $chunk);
								}
							}
							foreach ($result["structures"] ?? [] as $structureData) {
								$structure = new Structure(StructureType::from($structureData["name"]), Structure::parseBoundingBox($structureData["boundingBox"]));
								StructureManager::getInstance()->putStructure($structure);

								if (StructureGenerateEvent::hasHandlers()) {
									(new StructureGenerateEvent($world, $structure))->call();
								}
							}
							if ($chunk?->isTerrainDirty()) {
								$world->setChunk($chunkX, $chunkZ, $chunk);
							}
						}
					}
				}
			}, $i);
		}
	}
}