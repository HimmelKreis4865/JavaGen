<?php

declare(strict_types=1);

namespace JavaGen\helper;

use pocketmine\Server;
use pocketmine\world\World;

enum Dimension: string {

	case OVERWORLD = "world";

	case NETHER = "world_nether";

	case END = "world_the_end";

	public function findWorld(): ?World {
		$worldManager = Server::getInstance()->getWorldManager();
		$dimensions = GeneratorNames::fromDimension($this);
		foreach ($worldManager->getWorlds() as $world) {
			if ($world->getProvider()->getWorldData()->getGenerator() === $dimensions) {
				return $world;
			}
		}
		return null;
	}
}