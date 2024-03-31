<?php

declare(strict_types=1);

namespace JavaGen\event;

use JavaGen\structure\Structure;
use pocketmine\event\world\WorldEvent;
use pocketmine\world\World;

class StructureGenerateEvent extends WorldEvent {

	public function __construct(World $world, private Structure $structure) {
		parent::__construct($world);
	}

	public function getStructure(): Structure {
		return clone $this->structure;
	}
}