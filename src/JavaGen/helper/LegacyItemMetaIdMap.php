<?php

declare(strict_types=1);

namespace JavaGen\helper;

use pocketmine\data\bedrock\BedrockDataFiles;
use pocketmine\utils\SingletonTrait;
use function file_get_contents;
use function json_decode;

final class LegacyItemMetaIdMap {
	use SingletonTrait;

	private array $idToNamesMap = [];

	public function __construct() {
		$jsonData = json_decode(file_get_contents(BedrockDataFiles::R16_TO_CURRENT_ITEM_MAP_JSON), true);
		foreach ($jsonData["complex"] as $basename => $complexEntry) {
			foreach ($complexEntry as $meta => $name) {
				$this->idToNamesMap[$basename][$meta] = $name;
			}
		}
	}

	public function getMeta(string $name, int $meta): string {
		return $this->idToNamesMap[$name][$meta] ?? $name;
	}
}