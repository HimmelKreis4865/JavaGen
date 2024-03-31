<?php

declare(strict_types=1);

namespace JavaGen\helper\biome;

use InvalidArgumentException;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\utils\SingletonTrait;
use function array_keys;

final class BiomeIdentifierRegistry {
	use SingletonTrait;

	/**
	 * @var int[] $mappings
	 * @phpstan-var array<string, int> $mappings
	 */
	private array $mappings;

	public function __construct() {
		$this->registerDefaultBiomes();
	}

	private function registerDefaultBiomes(): void {
		$this->registerMapping("badlands", BiomeIds::MESA);
		$this->registerMapping("bamboo_jungle", BiomeIds::BAMBOO_JUNGLE);
		$this->registerMapping("basalt_deltas", BiomeIds::BASALT_DELTAS);
		$this->registerMapping("beach", BiomeIds::BEACH);
		$this->registerMapping("birch_forest", BiomeIds::BIRCH_FOREST);
		$this->registerMapping("cherry_grove", BiomeIds::CHERRY_GROVE);
		$this->registerMapping("cold_ocean", BiomeIds::COLD_OCEAN);
		$this->registerMapping("crimson_forest", BiomeIds::CRIMSON_FOREST);
		$this->registerMapping("dark_forest", BiomeIds::DEEP_DARK);
		$this->registerMapping("deep_cold_ocean", BiomeIds::DEEP_COLD_OCEAN);
		$this->registerMapping("deep_dark", BiomeIds::DEEP_DARK);
		$this->registerMapping("deep_frozen_ocean", BiomeIds::DEEP_FROZEN_OCEAN);
		$this->registerMapping("deep_lukewarm_ocean", BiomeIds::DEEP_LUKEWARM_OCEAN);
		$this->registerMapping("deep_ocean", BiomeIds::DEEP_OCEAN);
		$this->registerMapping("desert", BiomeIds::DESERT);
		$this->registerMapping("dripstone_caves", BiomeIds::DRIPSTONE_CAVES);
		$this->registerMapping("end_barrens", BiomeIds::THE_END);
		$this->registerMapping("end_highlands", BiomeIds::THE_END);
		$this->registerMapping("end_midlands", BiomeIds::THE_END);
		$this->registerMapping("eroded_badlands", BiomeIds::MESA_BRYCE);
		$this->registerMapping("flower_forest", BiomeIds::FLOWER_FOREST);
		$this->registerMapping("forest", BiomeIds::FOREST);
		$this->registerMapping("frozen_ocean", BiomeIds::FROZEN_OCEAN);
		$this->registerMapping("frozen_peaks", BiomeIds::FROZEN_PEAKS);
		$this->registerMapping("frozen_river", BiomeIds::FROZEN_RIVER);
		$this->registerMapping("grove", BiomeIds::GROVE);
		$this->registerMapping("ice_spikes", BiomeIds::ICE_PLAINS_SPIKES);
		$this->registerMapping("jagged_peaks", BiomeIds::JAGGED_PEAKS);
		$this->registerMapping("jungle", BiomeIds::JUNGLE);
		$this->registerMapping("lukewarm_ocean", BiomeIds::LUKEWARM_OCEAN);
		$this->registerMapping("lush_caves", BiomeIds::LUSH_CAVES);
		$this->registerMapping("mangrove_swamp", BiomeIds::MANGROVE_SWAMP);
		$this->registerMapping("meadow", BiomeIds::MEADOW);
		$this->registerMapping("mushroom_fields", BiomeIds::MUSHROOM_ISLAND);
		$this->registerMapping("nether_wastes", BiomeIds::HELL);
		$this->registerMapping("ocean", BiomeIds::OCEAN);
		$this->registerMapping("ocean", BiomeIds::OCEAN);
		$this->registerMapping("old_growth_birch_forest", BiomeIds::BIRCH_FOREST_MUTATED);
		$this->registerMapping("old_growth_pine_taiga", BiomeIds::MEGA_TAIGA);
		$this->registerMapping("old_growth_spruce_taiga", BiomeIds::REDWOOD_TAIGA_MUTATED);
		$this->registerMapping("plains", BiomeIds::PLAINS);
		$this->registerMapping("river", BiomeIds::RIVER);
		$this->registerMapping("savanna", BiomeIds::SAVANNA);
		$this->registerMapping("savanna_plateau", BiomeIds::SAVANNA_PLATEAU);
		$this->registerMapping("small_end_islands", BiomeIds::THE_END);
		$this->registerMapping("snowy_beach", BiomeIds::COLD_BEACH);
		$this->registerMapping("snowy_plains", BiomeIds::ICE_PLAINS);
		$this->registerMapping("snowy_slopes", BiomeIds::SNOWY_SLOPES);
		$this->registerMapping("snowy_taiga", BiomeIds::COLD_TAIGA);
		$this->registerMapping("soul_sand_valley", BiomeIds::SOULSAND_VALLEY);
		$this->registerMapping("sparse_jungle", BiomeIds::JUNGLE_EDGE);
		$this->registerMapping("stony_peaks", BiomeIds::STONY_PEAKS);
		$this->registerMapping("stony_shore", BiomeIds::STONE_BEACH);
		$this->registerMapping("sunflower_plains", BiomeIds::SUNFLOWER_PLAINS);
		$this->registerMapping("swamp", BiomeIds::SWAMPLAND);
		$this->registerMapping("taiga", BiomeIds::TAIGA);
		$this->registerMapping("the_end", BiomeIds::THE_END);
		$this->registerMapping("the_void", BiomeIds::THE_END);
		$this->registerMapping("warm_ocean", BiomeIds::WARM_OCEAN);
		$this->registerMapping("warped_forest", BiomeIds::WARPED_FOREST);
		$this->registerMapping("windswept_forest", BiomeIds::EXTREME_HILLS_PLUS_TREES);
		$this->registerMapping("windswept_gravelly_hills", BiomeIds::EXTREME_HILLS_MUTATED);
		$this->registerMapping("windswept_hills", BiomeIds::EXTREME_HILLS);
		$this->registerMapping("windswept_savanna", BiomeIds::SAVANNA_MUTATED);
		$this->registerMapping("windswept_savanna_plateau", BiomeIds::SAVANNA_PLATEAU_MUTATED);
		$this->registerMapping("wooded_badlands", BiomeIds::MESA_PLATEAU_STONE);
	}

	public function registerMapping(string $identifier, int $biomeId, bool $overwrite = false): bool {
		if (isset($this->mappings[$identifier]) and !$overwrite) return false;
		$this->mappings[$identifier] = $biomeId;
		return true;
	}

	public function getBiomeNames(): array {
		return array_keys($this->mappings);
	}

	public function get(string $identifier, bool $throwOnMissing = true): ?int {
		return $this->mappings[$identifier] ?? ($throwOnMissing ? throw new InvalidArgumentException("Failed to find the biome " . $identifier) : null);
	}
}