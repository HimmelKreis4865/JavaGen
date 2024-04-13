<?php

declare(strict_types=1);

namespace JavaGen\loot;

use JavaGen\helper\number\Number;
use JavaGen\loot\item\LootItem;
use pocketmine\item\Item;
use pocketmine\utils\Random;
use function ceil;

class LootPool {

	/** @var LootItem[] $itemPool */
	private array $itemPool = [];

	private ?int $cachedFullWeight = null;

	public function __construct(private Number $size) {}

	public function addItem(LootItem $item): LootPool {
		$this->itemPool[] = $item;
		return $this;
	}

	public static function fromSize(Number $size): LootPool {
		return new LootPool($size);
	}

	/**
	 * @return Item[]
	 */
	public function generateItems(Random $random): array {
		$size = $this->size->getNumber();
		$fullWeight = $this->getFullWeight();
		/** @var Item[] $items */
		$items = [];

		for ($i = 0; $i < $size; $i++) {
			for ($j = 0; $j < 100; $j++) {
				foreach ($this->itemPool as $item) {
					$item = clone $item;
					$chance = ($item->getWeight() / $fullWeight) * 10000;

					if ($random->nextBoundedInt(10000) < $chance) {
						$selectedItem = clone $item;
						$items[] = $selectedItem->runSelection($random);
						break 2;
					}
				}
			}
		}
		return $items;
	}

	public function getFullWeight(): int {
		if ($this->cachedFullWeight !== null) return $this->cachedFullWeight;
		$weightSum = 0;
		foreach ($this->itemPool as $item) {
			$weightSum += $item->getWeight();
		}
		return $this->cachedFullWeight = (int) ceil($weightSum);
	}
}