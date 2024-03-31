<?php

declare(strict_types=1);

namespace JavaGen\structure;

enum StructureCategory {

	case RUINED_PORTAL;

	case MINESHAFT;

	case PYRAMID;

	case VILLAGE;

	/**
	 * @return StructureType[]
	 */
	public function getChildren(): array {
		return match($this) {
			self::RUINED_PORTAL => [
				StructureType::RUINED_PORTAL,
				StructureType::RUINED_PORTAL_DESERT,
				StructureType::RUINED_PORTAL_JUNGLE,
				StructureType::RUINED_PORTAL_MOUNTAIN,
				StructureType::RUINED_PORTAL_NETHER,
				StructureType::RUINED_PORTAL_OCEAN,
				StructureType::RUINED_PORTAL_SWAMP,
			],
			self::MINESHAFT => [
				StructureType::MINESHAFT,
				StructureType::MINESHAFT_MESA
			],
			self::PYRAMID => [
				StructureType::DESERT_PYRAMID,
				StructureType::JUNGLE_PYRAMID,
			],
			self::VILLAGE => [
				StructureType::VILLAGE_DESERT,
				StructureType::VILLAGE_PLAINS,
				StructureType::VILLAGE_SAVANNA,
				StructureType::VILLAGE_SNOWY,
				StructureType::VILLAGE_TAIGA,
			]
		};
	}
}