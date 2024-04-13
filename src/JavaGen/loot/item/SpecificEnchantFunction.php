<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\RandomNumber;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Random;
use function min;
use function strtoupper;

class SpecificEnchantFunction extends LootItemFunction {

	public function __construct(private array $enchants) {
	}

	public function applyOn(LootItem $lootItem, Random $random): void {
		$item = $lootItem->item;
		if ($item->getTypeId() === ItemTypeIds::BOOK) {
			$item = $lootItem->item = VanillaItems::ENCHANTED_BOOK();
		}
		foreach ($this->enchants as $enchantData) {
			$enchants = VanillaEnchantments::getAll();
			if (!isset($enchants[strtoupper($enchantData["id"] ?? "")])) return;

			$enchantment = $enchants[strtoupper($enchantData["id"])];
			$num = new RandomNumber($z = $enchantData["level"][0], $enchantData["level"][1] ?? $z);
			$instance = new EnchantmentInstance($enchantment, min((int) $num->getNumber(), $enchantment->getMaxLevel()));

			$lootItem->item->addEnchantment($instance);
		}
	}

	/**
	 * @phpstan-param array<mixed> $data
	 */
	public static function fromJson(array $data): SpecificEnchantFunction {
		return new SpecificEnchantFunction($data["enchants"]);
	}
}