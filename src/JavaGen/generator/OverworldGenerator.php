<?php

declare(strict_types=1);

namespace JavaGen\generator;

use JavaGen\helper\Dimension;
use pocketmine\data\bedrock\BiomeIds;

final class OverworldGenerator extends BaseJavaGenerator {

	protected const MIN_Y = -64;

	protected const MAX_Y = 320;

	protected const DEFAULT_BIOME = BiomeIds::OCEAN;

	public function getDimension(): Dimension {
		return Dimension::OVERWORLD;
	}
}