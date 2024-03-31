<?php

declare(strict_types=1);

namespace JavaGen\tile;

use Closure;
use InvalidArgumentException;
use JavaGen\loot\LootTableRegistry;
use pocketmine\block\tile\Bed;
use pocketmine\block\tile\BrewingStand;
use pocketmine\block\tile\Chest;
use pocketmine\block\tile\EnderChest;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\utils\Random;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function mt_rand;
use function str_replace;

final class JavaTileMappings {
	use SingletonTrait;

	/**
	 * @var Closure[] $mappings
	 * @phpstan-var array<string, Closure(JavaTile, World, Chunk): void> $mappings
	 */
	private array $mappings = [];

	public function __construct() {
		$this->registerDefaultMappings();
	}

	private function registerDefaultMappings(): void {
		$this->registerMapping(BlockTypeNames::CHEST, function (JavaTile $tile, World $world, Chunk $chunk): void {
			if (!isset($tile->getAdditionalData()["LootTable"])) {
				return;
			}
			$name = str_replace("minecraft:", "", $tile->getAdditionalData()["LootTable"]);

			if (($lootTable = LootTableRegistry::getInstance()->getTableByName($name)) !== null) {
				$tileChest = new Chest($world, $tile->getPosition());
				$tileChest->getInventory()->setContents($lootTable->placeItemsInChestGrid(new Random($tile->getAdditionalData()["LootTableSeed"] ?? mt_rand())));
				$chunk->addTile($tileChest);
				$chunk->setTerrainDirty();
			} else {
				throw new InvalidArgumentException("LootTable " . $name . " does not exist!");
			}
		});
		$this->registerMapping(BlockTypeNames::ENDER_CHEST, function (JavaTile $tile, World $world, Chunk $chunk): void {
			$tileBed = new EnderChest($world, $tile->getPosition());
			$chunk->addTile($tileBed);
			$chunk->setTerrainDirty();
		});
		$this->registerMapping(BlockTypeNames::BED, function (JavaTile $tile, World $world, Chunk $chunk): void {
			$tileBed = new Bed($world, $tile->getPosition());
			$chunk->addTile($tileBed);
			$chunk->setTerrainDirty();
		});
		$this->registerMapping(BlockTypeNames::BREWING_STAND, function (JavaTile $tile, World $world, Chunk $chunk): void {
			$tileBed = new BrewingStand($world, $tile->getPosition()); // todo: inventory items
			$chunk->addTile($tileBed);
			$chunk->setTerrainDirty();
		});
	}

	/**
	 * @phpstan-param Closure(JavaTile, World, Chunk): void $closure
	 */
	public function registerMapping(string $tileId, Closure $closure, bool $overwrite = false): bool {
		if (isset($this->mappings[$tileId]) and !$overwrite) return false;
		$this->mappings[$tileId] = $closure;
		return true;
	}

	/**
	 * @phpstan-return (Closure(JavaTile, World, Chunk): void)|null
	 */
	public function findMapping(string $tileId): ?Closure {
		return $this->mappings[$tileId] ?? null;
	}
}