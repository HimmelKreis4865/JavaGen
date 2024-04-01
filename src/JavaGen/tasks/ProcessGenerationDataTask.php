<?php

declare(strict_types=1);

namespace JavaGen\tasks;

use JavaGen\event\StructureGenerateEvent;
use JavaGen\generator\BaseJavaGenerator;
use JavaGen\helper\Dimension;
use JavaGen\structure\Structure;
use JavaGen\structure\StructureManager;
use JavaGen\structure\StructureType;
use JavaGen\tile\JavaTile;
use JavaGen\tile\JavaTileMappings;
use JsonException;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\AsyncWorker;
use pocketmine\thread\Thread;
use function is_array;
use function is_string;
use function json_decode;
use const JSON_THROW_ON_ERROR;

class ProcessGenerationDataTask extends AsyncTask {

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

	/**
	 * @throws JsonException
	 */
	public function onCompletion(): void {
		if ($this->hasResult() and is_string($result = $this->getResult())) {
			$resultStack = json_decode($result, true, flags: JSON_THROW_ON_ERROR);
			if (!is_array($resultStack)) {
				return;
			}

			foreach ($resultStack as $result) {
				$expectedDimension = (string) $result["dimension"];
				$chunkX = (int) $result["chunkX"];
				$chunkZ = (int) $result["chunkZ"];

				$world = Dimension::tryFrom($expectedDimension)?->findWorld();
				if ($world === null) {
					return;
				}
				$chunk = $world->getChunk($chunkX, $chunkZ);
				if ($chunk !== null) {
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
	}
}