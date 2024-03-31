<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Random;

class LootItem {

	private int|float $weight = 1;

	/** @var LootItemFunction[] $onSelectFunctions */
	private array $onSelectFunctions = [];

	protected function __construct(public Item $item) {}

	public function setWeight(int|float $weight): LootItem {
		$this->weight = $weight;
		return $this;
	}

	public function getWeight(): int|float {
		return $this->weight;
	}

	public function apply(LootItemFunction $function): LootItem {
		$this->onSelectFunctions[] = $function;
		return $this;
	}

	public function runSelection(Random $random): Item {
		$i = new LootItem(clone $this->item);
		foreach ($this->onSelectFunctions as $function) {
			$function->applyOn($i, $random);
		}
		return $i->item;
	}

	public static function fromItem(Item $item): LootItem {
		return new LootItem($item);
	}

	public static function empty(): LootItem {
		return LootItem::fromItem(VanillaItems::AIR());
	}
}