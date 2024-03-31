<?php

declare(strict_types=1);

namespace JavaGen\data;

use JavaGen\JavaGen;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use function str_replace;

final class Messages {
	use SingletonTrait;

	private Config $messageKeys;

	private array $messageKeyCache = [];

	public function __construct() {
		$this->messageKeys = new Config(JavaGen::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
	}

	public function get(MessageKey $key, string|int|float ...$replaces): string {
		$message = $this->messageKeyCache[$key->value] ??= $this->messageKeys->getNested($key->value) ?? "Invalid message key " . $key->value . " for plugin JavaGen.";
		foreach ($replaces as $i => $replace) {
			$message = str_replace("%" . $i, (string) $replace, $message);
		}
		return $message;
	}
}