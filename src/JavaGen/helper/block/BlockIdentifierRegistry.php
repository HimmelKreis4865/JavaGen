<?php

declare(strict_types=1);

namespace JavaGen\helper\block;

use Closure;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\utils\SingletonTrait;

final class BlockIdentifierRegistry {
	use SingletonTrait;

	/**
	 * @var string[] $mappings
	 * @phpstan-var array<string, string> $mappings
	 */
	private array $mappings;

	/**
	 * @var Closure[] $defaultBlocks
	 * @phpstan-var array<string, Closure(array<string, scalar>): Block> $defaultBlocks
	 */
	private array $defaultBlocks = [];

	public function __construct() {
		$this->registerDefaultMappings();
	}

	private function registerDefaultMappings(): void {
		$this->registerMapping("minecraft:cave_air", BlockTypeNames::AIR);
		$this->registerMapping("minecraft:deepslate_lapis_ore", BlockTypeNames::DEEPSLATE_LAPIS_ORE);
		$this->registerMapping("minecraft:kelp", BlockTypeNames::WATER);
		$this->registerMapping("minecraft:kelp_plant", BlockTypeNames::WATER);
		$this->registerMapping("minecraft:magma_block", BlockTypeNames::MAGMA);
		$this->registerMapping("minecraft:short_grass", BlockTypeNames::TALLGRASS);
		$this->registerMapping("minecraft:seagrass", BlockTypeNames::WATER);
		$this->registerMapping("minecraft:tall_seagrass", BlockTypeNames::WATER);
		// these blocks simply don't exist in pocketmine:
		$this->registerMapping("minecraft:bamboo_block", BlockTypeNames::AIR, fn() => VanillaBlocks::AIR());
		$this->registerMapping("minecraft:stripped_bamboo_block", BlockTypeNames::AIR, fn() => VanillaBlocks::AIR());
		$this->registerMapping("minecraft:bamboo_mosaic", BlockTypeNames::OAK_PLANKS);
		$this->registerMapping("minecraft:bamboo_planks", BlockTypeNames::OAK_PLANKS);
		$this->registerMapping("minecraft:bamboo_stairs", BlockTypeNames::OAK_STAIRS);
		$this->registerMapping("minecraft:cherry_sapling", BlockTypeNames::AIR, fn() => VanillaBlocks::AIR());
		$this->registerMapping("minecraft:dispenser", BlockTypeNames::AIR, fn() => VanillaBlocks::AIR());
		$this->registerMapping("minecraft:mangrove_propagule", BlockTypeNames::AIR, fn() => VanillaBlocks::AIR());
		$this->registerMapping("minecraft:suspicious_sand", BlockTypeNames::SAND, fn() => VanillaBlocks::SAND());
		$this->registerMapping("minecraft:suspicious_gravel", BlockTypeNames::GRAVEL, fn() => VanillaBlocks::GRAVEL());
	}

	/**
	 * @phpstan-param Closure(array<string, scalar>): Block|null $stateMapper
	 */
	public function registerMapping(string $missingIdentifier, string $realIdentifier, ?Closure $stateMapper = null, bool $overwrite = false): bool {
		if ((isset($this->mappings[$missingIdentifier]) or isset($this->defaultBlocks[$missingIdentifier])) and !$overwrite) return false;
		$this->mappings[$missingIdentifier] = $realIdentifier;
		if ($stateMapper === null) {
			unset($this->defaultBlocks[$missingIdentifier]);
		} else {
			$this->defaultBlocks[$missingIdentifier] = $stateMapper;
		}
		return true;
	}

	public function map(string $identifier): string {
		return $this->mappings[$identifier] ?? $identifier;
	}

	/**
	 * @param scalar[] $states
	 * @phpstan-param array<string, scalar> $states
	 */
	public function getBlock(string $identifier, array $states): ?Block {
		if (!isset($this->defaultBlocks[$identifier])) return null;
		return ($this->defaultBlocks[$identifier])($states);
	}
}