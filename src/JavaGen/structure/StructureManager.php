<?php

declare(strict_types=1);

namespace JavaGen\structure;

use JavaGen\database\ChunkDataStorage;
use JavaGen\helper\Dimension;
use JavaGen\helper\GeneratorNames;
use pocketmine\math\AxisAlignedBB;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;
use pocketmine\world\World;

final class StructureManager {
	use SingletonTrait;

	/**
	 * @var Structure[][] $registeredStructures
	 * @phpstan-var array<string, array<int, Structure>> $registeredStructures
	 */
	private array $registeredStructures = [];

	public function __construct() {
		foreach (Dimension::cases() as $dimension) {
			foreach (ChunkDataStorage::getInstance()->getStructuresInDimension($dimension) as $structure) {
				$this->registeredStructures[$dimension->value][] = $structure;
			}
		}
	}

	public function __destruct() {
		foreach ($this->registeredStructures as $dimension => $structures) {
			foreach ($structures as $structure) {
				ChunkDataStorage::getInstance()->storeStructure($structure, Dimension::from($dimension));
			}
		}
	}

	public function putStructure(Structure $structure): void {
		$this->registeredStructures[$structure->getType()->getDimension()->value][] = $structure;
	}

	public function getStructuresAt(Position $position): array {
		$dimension = GeneratorNames::toDimension($position->getWorld());

		if ($dimension === null) return [];
		$structures = [];
		foreach ($this->registeredStructures[$dimension->value] ?? [] as $structure) {
			if ($structure->getBoundingBox()->isVectorInside($position)) {
				$structures[] = $structure;
			}
		}
		return $structures;
	}

	public function getStructuresInChunk(World $world, int $chunkX, int $chunkZ): array {
		$aabb = new AxisAlignedBB($chunkX << 4, $world->getMinY(), $chunkZ << 4, ($chunkX << 4) + 15, $world->getMaxY(), ($chunkZ << 4) + 15);
		$dimension = GeneratorNames::toDimension($world);

		if ($dimension === null) return [];
		$structures = [];
		foreach ($this->registeredStructures[$dimension->value] ?? [] as $structure) {
			if ($structure->getBoundingBox()->intersectsWith($aabb)) {
				$structures[] = $structure;
			}
		}
		return $structures;
	}
}