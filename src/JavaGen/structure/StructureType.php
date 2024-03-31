<?php

declare(strict_types=1);

namespace JavaGen\structure;

use JavaGen\helper\Dimension;
use function in_array;

enum StructureType: string {

	case ANCIENT_CITY = "ancient_city";

	case BASTION_REMNANT = "bastion_remnant";

	case BURIED_TREASURE = "buried_treasure";

	case DESERT_PYRAMID = "desert_pyramid";

	case END_CITY = "end_city";

	case FORTRESS = "fortress";

	case IGLOO = "igloo";

	case JUNGLE_PYRAMID = "jungle_pyramid";

	case MANSION = "mansion";

	case MINESHAFT = "mineshaft";

	case MINESHAFT_MESA = "mineshaft_mesa";

	case MONUMENT = "monument";

	case NETHER_FOSSIL = "nether_fossil";

	case OCEAN_RUIN_COLD = "ocean_ruin_cold";

	case OCEAN_RUIN_WARM = "ocean_ruin_warm";

	case PILLAGER_OUTPOST = "pillager_outpost";

	case RUINED_PORTAL = "ruined_portal";

	case RUINED_PORTAL_DESERT = "ruined_portal_desert";

	case RUINED_PORTAL_JUNGLE = "ruined_portal_jungle";

	case RUINED_PORTAL_MOUNTAIN = "ruined_portal_mountain";

	case RUINED_PORTAL_NETHER = "ruined_portal_nether";

	case RUINED_PORTAL_OCEAN = "ruined_portal_ocean";

	case RUINED_PORTAL_SWAMP = "ruined_portal_swamp";

	case SHIPWRECK = "shipwreck";

	case SHIPWRECK_BEACHED = "shipwreck_beached";

	case STRONGHOLD = "stronghold";

	case SWAMP_HUT = "swamp_hut";

	case TRAIL_RUINS = "trail_ruins";

	case TRIAL_CHAMBERS = "trial_chambers";

	case VILLAGE_DESERT = "village_desert";

	case VILLAGE_PLAINS = "village_plains";

	case VILLAGE_SAVANNA = "village_savanna";

	case VILLAGE_SNOWY = "village_snowy";

	case VILLAGE_TAIGA = "village_taiga";

	public function inCategory(StructureCategory $category): bool {
		return in_array($this, $category->getChildren(), true);
	}

	public function getDimension(): Dimension {
		return match($this) {
			self::FORTRESS, self::NETHER_FOSSIL, self::RUINED_PORTAL_NETHER, self::BASTION_REMNANT => Dimension::NETHER,
			self::END_CITY => Dimension::END,
			default => Dimension::OVERWORLD
		};
	}
}