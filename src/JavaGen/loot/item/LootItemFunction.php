<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use pocketmine\utils\Random;

abstract class LootItemFunction {

	abstract public function applyOn(LootItem $item, Random $random): void;

	abstract public static function fromJson(array $data): static;
}