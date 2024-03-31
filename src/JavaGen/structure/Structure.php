<?php

declare(strict_types=1);

namespace JavaGen\structure;

use InvalidArgumentException;
use pocketmine\math\AxisAlignedBB;
use function array_map;
use function array_slice;
use function count;
use function preg_match;

class Structure {

	private const PATTERN_BOUNDING_BOX = "/\{minX=([0-9\-]+), minY=([0-9\-]+), minZ=([0-9\-]+), maxX=([0-9\-]+), maxY=([0-9\-]+), maxZ=([0-9\-]+)\}/";

	public function __construct(private readonly StructureType $type, private readonly AxisAlignedBB $boundingBox) {
	}

	public function getType(): StructureType {
		return $this->type;
	}

	public function getBoundingBox(): AxisAlignedBB {
		return $this->boundingBox;
	}

	public static function parseBoundingBox(string $boundingBox): ?AxisAlignedBB {
		preg_match(self::PATTERN_BOUNDING_BOX, $boundingBox, $matches);

		if (count($matches) !== 7) {
			throw new InvalidArgumentException("Malformed bounding box: " . $boundingBox);
		}
		return new AxisAlignedBB(...array_map("intval", array_slice($matches, 1)));
	}
}