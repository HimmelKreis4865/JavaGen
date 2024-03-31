<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\Number;
use pocketmine\utils\Random;
use function min;

class SetCountFunction extends LootItemFunction {

	public function __construct(private Number $count) {
	}

	public function applyOn(LootItem $item, Random $random): void {
		$num = $this->count->getNumber();
		$item->item->setCount(min($item->item->getMaxStackSize(), (int) $num));
	}


	/**
	 * @phpstan-param array<mixed> $data
	 */
	public static function fromJson(array $data): SetCountFunction {
		return new SetCountFunction(Number::fromJson($data["count"]));
	}
}