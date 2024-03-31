<?php

declare(strict_types=1);

namespace JavaGen\generator;

use JavaGen\helper\Dimension;
use pocketmine\data\bedrock\BiomeIds;

final class NetherGenerator extends BaseJavaGenerator {

	protected const MIN_Y = 0;

	protected const MAX_Y = 256;

	protected const DEFAULT_BIOME = BiomeIds::HELL;

	public function getDimension(): Dimension {
		return Dimension::NETHER;
	}
}