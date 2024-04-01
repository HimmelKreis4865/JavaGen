<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\Number;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Random;
use ReflectionClass;
use function array_rand;
use function count;
use function mt_rand;

class EnchantItemFunction extends LootItemFunction {

	public function __construct(private ?Number $levels = null) {}

	public function applyOn(LootItem $lootItem, Random $random): void {
		$item = $lootItem->item;

		if ($item->getTypeId() === ItemTypeIds::BOOK) {
			$item = $lootItem->item = VanillaItems::ENCHANTED_BOOK();
		}
		if ($this->levels === null) {
			$enchants = AvailableEnchantmentRegistry::getInstance()->getAllEnchantmentsForItem($item);
			if (count($enchants) > 0) {
				$enchant = $enchants[array_rand($enchants)];

				$item->addEnchantment(new EnchantmentInstance($enchant, $random->nextRange(1, $enchant->getMaxLevel())));
			}
		} else {
			$method = (new ReflectionClass(EnchantingHelper::class))->getMethod("createOption");
			/** @var EnchantingOption $option */
			$option = $method->getClosure()($random, $item, (int) $this->levels->getNumber());

			foreach ($option->getEnchantments() as $enchantment) {
				$item->addEnchantment($enchantment);
			}
		}
	}

	/**
	 * @phpstan-param array<mixed> $data
	 */
	public static function fromJson(array $data): EnchantItemFunction {
		if (isset($data["levels"])) {
			return new EnchantItemFunction(Number::fromJson($data["levels"]));
		}
		return new EnchantItemFunction();
	}
}