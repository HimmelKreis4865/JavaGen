<?php

declare(strict_types=1);

namespace JavaGen\loot;

use pocketmine\item\Item;
use pocketmine\utils\Random;
use function array_shift;
use function count;

class LootTable {

	/** @var LootPool[] $pools */
	private array $pools;

	public function __construct(LootPool ...$pools) {
		$this->pools = $pools;
	}

	/**
	 * @return Item[]
	 */
	public function generateItems(Random $random): array {
		$items = [];
		foreach ($this->pools as $pool) {
			foreach ($pool->generateItems($random) as $item) {
				$items[] = $item;
			}
		}
		return $items;
	}

	public function placeItemsInChestGrid(Random $random): array {
		$unsortedItems = $this->generateItems($random);
		foreach ($unsortedItems as $item) {
			if ($item->getCount() > 5) {
				$j = 3 + $random->nextBoundedInt($item->getCount() - 5);
				for ($i = 0; $i < $j; $i++) {
					if ($item->getCount() > 2) {
						$unsortedItems[] = $item->pop();
					}
				}
			}
		}
		$items = [];
		while (count($unsortedItems) and count($items) < 18) {
			$selectedIndex = $random->nextBoundedInt(27);
			if (isset($items[$selectedIndex])) continue;

			$items[$selectedIndex] = array_shift($unsortedItems);
		}
		return $items;
	}
}