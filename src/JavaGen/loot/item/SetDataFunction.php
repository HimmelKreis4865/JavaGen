<?php

declare(strict_types=1);

namespace JavaGen\loot\item;

use JavaGen\helper\LegacyItemMetaIdMap;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Random;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class SetDataFunction extends LootItemFunction {

	public function __construct(private readonly int $data) {
	}

	public function applyOn(LootItem $item, Random $random): void {
		$data = GlobalItemDataHandlers::getSerializer()->serializeType($item->item);

		$resultingName = LegacyItemMetaIdMap::getInstance()->getMeta($data->getName(), $this->data);
		if ($resultingItem = StringToItemParser::getInstance()->parse($resultingName)) {
			$item->item = $resultingItem;
		}
	}

	public static function fromJson(array $data): static {
		return new SetDataFunction((int) $data["data"]);
	}
}