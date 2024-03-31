<?php

declare(strict_types=1);

namespace JavaGen;

use JavaGen\commands\LocateCommand;
use JavaGen\generator\EndGenerator;
use JavaGen\generator\NetherGenerator;
use JavaGen\generator\OverworldGenerator;
use JavaGen\helper\Dimension;
use JavaGen\helper\GeneratorNames;
use JavaGen\stream\JavaRequests;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\GeneratorManager;

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
		$task = new class extends AsyncTask {

			public function onRun(): void {
				JavaRequests::requestChunk(Dimension::OVERWORLD, 0, 0, $stream);
			}
		};
		$this->getServer()->getAsyncPool()->submitTask($task);
	}

	private function validateConfig(): void {
		$this->saveResource("messages.yml");
		// todo: make address & port variable
		//$this->saveDefaultConfig();
		//assert(is_string($this->getConfig()->get("address")), new RuntimeException("Config field \"address\" is missing or not a string"));
		//assert(is_int($this->getConfig()->get("port")), new RuntimeException("Config field \"port\" is missing or not a number"));
	}
}