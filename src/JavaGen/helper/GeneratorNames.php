<?php

declare(strict_types=1);

namespace JavaGen\helper;

use pocketmine\world\World;

final class GeneratorNames {

	public const OVERWORLD = "java_overworld";

	public const NETHER = "java_nether";

	public const END = "java_end";

	public static function isOneOf(string $generator): bool {
		return $generator === self::OVERWORLD or $generator === self::NETHER or $generator === self::END;
	}

	public static function toDimension(World|string $world_or_generator): ?Dimension {
		if ($world_or_generator instanceof World) {
			$world_or_generator = $world_or_generator->getProvider()->getWorldData()->getGenerator();
		}
		return match($world_or_generator) {
			self::OVERWORLD => Dimension::OVERWORLD,
			self::NETHER => Dimension::NETHER,
			self::END => Dimension::END,
			default => null
		};
	}

	public static function fromDimension(Dimension $dimension): string {
		return match ($dimension) {
			Dimension::OVERWORLD => self::OVERWORLD,
			Dimension::NETHER => self::NETHER,
			Dimension::END => self::END
		};
	}
}