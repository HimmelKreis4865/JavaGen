<?php

declare(strict_types=1);

namespace JavaGen;

use JavaGen\commands\LocateCommand;
use JavaGen\data\Messages;
use JavaGen\generator\EndGenerator;
use JavaGen\generator\NetherGenerator;
use JavaGen\generator\OverworldGenerator;
use JavaGen\helper\GeneratorNames;
use JavaGen\tasks\CheckConnectionTask;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\GeneratorManager;
use function rename;

final class JavaGen extends PluginBase {
	use SingletonTrait;

	protected function onLoad(): void {
		self::$instance = $this;
		$this->validateConfig();
		$this->testConnection();

		GeneratorManager::getInstance()->addGenerator(OverworldGenerator::class, GeneratorNames::OVERWORLD, fn() => null);
		GeneratorManager::getInstance()->addGenerator(NetherGenerator::class, GeneratorNames::NETHER, fn() => null);
		GeneratorManager::getInstance()->addGenerator(EndGenerator::class, GeneratorNames::END, fn() => null);
	}

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
		$this->getServer()->getCommandMap()->register("JavaGen", new LocateCommand());
	}

	protected function testConnection(): void {
		$this->getServer()->getAsyncPool()->submitTask(new CheckConnectionTask());
	}

	private function validateConfig(): void {
		$this->saveResource("messages.yml");

		$config = new Config($this->getResourcePath("messages.yml"), Config::YAML);
		if ($config->get("version") > Messages::getInstance()->getVersion()) {
			rename($this->getDataFolder() . "messages.yml", $this->getDataFolder() . "messages_old.yml");
			$this->saveResource("messages.yml");
			$this->getLogger()->warning("Your messages.yml configuration file is outdated! A new one was created and the old one was renamed to messages_old.yml");
			Messages::reset();
		}
		// todo: make address & port variable
		//$this->saveDefaultConfig();
		//assert(is_string($this->getConfig()->get("address")), new RuntimeException("Config field \"address\" is missing or not a string"));
		//assert(is_int($this->getConfig()->get("port")), new RuntimeException("Config field \"port\" is missing or not a number"));
	}
}