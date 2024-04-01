<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\Number;
use pocketmine\item\Durable;
use pocketmine\utils\Random;

class SetDamageFunction extends LootItemFunction {

	public function __construct(private Number $damage) {
	}

	public function applyOn(LootItem $lootItem, Random $random): void {
		if (!$lootItem instanceof Durable) return;

		$num = $this->damage->getNumber();
		if ($num > 1) {
			$lootItem->setDamage((int) $num);
		} else {
			$lootItem->setDamage($num * $lootItem->getMaxDurability());
		}
	}


	/**
	 * @phpstan-param array<mixed> $data
	 */
	public static function fromJson(array $data): SetDamageFunction {
		return new SetDamageFunction(Number::fromJson($data["damage"]));
	}
}