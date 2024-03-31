<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\number\Number;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\utils\Random;
use ReflectionClass;
use function array_rand;
use function count;
use function mt_rand;

class EnchantItemFunction extends LootItemFunction {

	/**
	 * @param bool $treasure todo: find out what this does
	 */
	public function __construct(private readonly ?Number $levels = null, private readonly bool $treasure = false) {
	}

	public function applyOn(LootItem $item, Random $random): void {
		$item = $item->item;

		if ($this->levels === null) {
			$enchants = AvailableEnchantmentRegistry::getInstance()->getAllEnchantmentsForItem($item);
			if (count($enchants) > 0) {
				$enchant = $enchants[array_rand($enchants)];

				$item->addEnchantment(new EnchantmentInstance($enchant, mt_rand(1, $enchant->getMaxLevel())));
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

	public static function fromJson(array $data): static {
		if (isset($data["levels"])) {
			return new EnchantItemFunction(Number::fromJson($data["levels"]), $data["treasure"]);
		}
		return new EnchantItemFunction();
	}
}