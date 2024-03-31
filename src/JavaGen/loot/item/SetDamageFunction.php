<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\Number;
use pocketmine\item\Durable;
use pocketmine\utils\Random;

class SetDamageFunction extends LootItemFunction {

	public function __construct(private Number $damage) {
	}

	public function applyOn(LootItem $item, Random $random): void {
		if (!$item instanceof Durable) return;

		$num = $this->damage->getNumber();
		if ($num > 1) {
			$item->setDamage((int) $num);
		} else {
			$item->setDamage($num * $item->getMaxDurability());
		}
	}


	/**
	 * @phpstan-param array<mixed> $data
	 */
	public static function fromJson(array $data): SetDamageFunction {
		return new SetDamageFunction(Number::fromJson($data["damage"]));
	}
}