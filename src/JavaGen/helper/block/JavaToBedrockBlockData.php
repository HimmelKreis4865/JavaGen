<?php

declare(strict_types=1);

namespace JavaGen\helper\block;

use InvalidArgumentException;
use JavaGen\data\DataRegistry;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockStateDeserializeException;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use function array_map;
use function file_get_contents;
use function is_bool;
use function is_float;
use function is_int;
use function json_decode;

final class JavaToBedrockBlockData {
	use SingletonTrait;

	public static string $mappingsFile;

	/** @var Block[] $mappings */
	private readonly array $mappings;

	public function __construct() {
		$this->mappings = array_map(function(array $json): Block {
			$identifier = BlockIdentifierRegistry::getInstance()->map($json["bedrock_identifier"]);
			$states = $this->jsonToNbt($json["bedrock_states"] ?? []);

			$block = BlockIdentifierRegistry::getInstance()->getBlock($json["bedrock_identifier"], $json["bedrock_states"] ?? []);
			if ($block !== null) return $block;

			$data = new BlockStateData($identifier, $states, 0);

			try {
				return GlobalBlockStateHandlers::getDeserializer()->deserializeBlock($data);
			} catch (BlockStateDeserializeException) {
				$result = StringToItemParser::getInstance()->parse($identifier)?->getBlock();
				return $result ?? VanillaBlocks::AIR();
			}
		}, json_decode(file_get_contents(DataRegistry::MAPPINGS_FILE), true));
	}

	public function javaToBedrockBlock(string $identifier): Block {
		return $this->mappings[$identifier] ?? throw new InvalidArgumentException("Block \"" . $identifier . "\" is not registered in the mappings");
	}

	private function jsonToNbt(array $json): array {
		$tags = [];
		foreach($json as $name => $value) {
			$tags[$name] = match(true) {
				is_float($value) => new FloatTag($value),
				is_bool($value) => new ByteTag((int) $value),
				is_int($value) => new IntTag($value),
				default => new StringTag($value)
			};
		}
		return $tags;
	}
}