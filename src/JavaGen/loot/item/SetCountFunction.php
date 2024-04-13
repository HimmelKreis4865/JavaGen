<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\Number;
use pocketmine\utils\Random;
use function min;

class SetCountFunction extends LootItemFunction {

	public function __construct(private Number $count) {
	}

	public function applyOn(LootItem $lootItem, Random $random): void {
		$num = $this->count->getNumber();
		$lootItem->item->setCount(min($lootItem->item->getMaxStackSize(), (int) $num));
	}

	/**
	 * @phpstan-param array<mixed> $data
	 */
	public static function fromJson(array $data): SetCountFunction {
		return new SetCountFunction(Number::fromJson($data["count"]));
	}
}