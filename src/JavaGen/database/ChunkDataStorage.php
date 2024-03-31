<?php

declare(strict_types=1);

namespace JavaGen\database;

use JavaGen\helper\Dimension;
use JavaGen\structure\Structure;
use JavaGen\structure\StructureType;
use pocketmine\math\AxisAlignedBB;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use function array_map;
use function explode;
use function getcwd;
use function in_array;
use const DIRECTORY_SEPARATOR;

final class ChunkDataStorage {
	use SingletonTrait;

	/** @var string[][][] */
	private array $structures = [];

	public function __construct() {
		foreach (Dimension::cases() as $dimension) {
			$config = new Config(self::structurePath($dimension), Config::JSON);

			$this->structures[$dimension->value] = $config->getAll(true);
		}
	}

	public function __destruct() {
		foreach ($this->structures as $dimension => $structures) {
			$dimension = Dimension::from($dimension);

			$config = new Config(self::structurePath($dimension), Config::JSON);
			$config->setAll($structures);
			$config->save();
		}
	}

	public function storeStructure(Structure $structure, Dimension $dimension): void {
		$encodedBoundingBox = self::encodeBoundingBox($structure->getBoundingBox());
		if (!in_array($encodedBoundingBox, $this->structures[$dimension->value][$structure->getType()->value] ?? [], true)) {
			$this->structures[$dimension->value][$structure->getType()->value][] = $encodedBoundingBox;
		}
	}

	public function getStructuresInDimension(Dimension $dimension): array {
		$structures = [];
		foreach	($this->structures[$dimension->value] as $type => $boundingBoxes) {
			$type = StructureType::from($type);
			foreach ($boundingBoxes as $boundingBox) {
				$structures[] = new Structure($type, self::decodeBoundingBox($boundingBox));
			}
		}
		return $structures;
	}

	public function structureExists(Structure $structure): bool {
		return in_array(
			self::encodeBoundingBox($structure->getBoundingBox()),
			$this->structures[$structure->getType()->getDimension()->value][$structure->getType()->value] ?? [],
			true
		);
	}

	private static function encodeBoundingBox(AxisAlignedBB $aabb): string {
		return $aabb->minX . "," . $aabb->minY . "," . $aabb->minZ . "," . $aabb->maxX . "," . $aabb->maxY . "," . $aabb->maxZ;
	}

	private static  function decodeBoundingBox(string $aabb): AxisAlignedBB {
		return new AxisAlignedBB(...array_map('intval', explode(",", $aabb)));
	}

	private static function structurePath(Dimension $dimension): string {
		return getcwd() . DIRECTORY_SEPARATOR . "worlds" . DIRECTORY_SEPARATOR . $dimension->value . ".json";
	}
}