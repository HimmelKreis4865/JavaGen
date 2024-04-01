<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use pocketmine\utils\Random;

abstract class LootItemFunction {

	abstract public function applyOn(LootItem $lootItem, Random $random): void;

	/**
	 * @phpstan-param array<mixed> $data
	 */
	abstract public static function fromJson(array $data): self;
}