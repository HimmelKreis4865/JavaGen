<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\RandomNumber;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\utils\Random;
use function min;
use function strtoupper;

class SpecificEnchantFunction extends LootItemFunction {

	public function __construct(private readonly array $enchants) {
	}

	public function applyOn(LootItem $item, Random $random): void {
		foreach ($this->enchants as $enchantData) {
			$enchants = VanillaEnchantments::getAll();
			if (!isset($enchants[strtoupper($enchantData["id"] ?? "")])) return;

			$enchantment = $enchants[strtoupper($enchantData["id"])];
			$num = new RandomNumber($z = $enchantData["level"][0], $enchantData["level"][1] ?? $z);
			$instance = new EnchantmentInstance($enchantment, min($num->getNumber(), $enchantment->getMaxLevel()));

			$item->item->addEnchantment($instance);
		}
	}

	public static function fromJson(array $data): static {
		return new SpecificEnchantFunction($data["enchants"]);
	}
}