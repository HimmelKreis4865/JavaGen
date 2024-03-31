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
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\Position;
use pocketmine\world\WorldCreationOptions;
use RuntimeException;
use function array_diff;
use function assert;
use function is_dir;
use function is_int;
use function is_string;
use function rmdir;
use function rtrim;
use function scandir;
use function unlink;
use const DIRECTORY_SEPARATOR;

final class JavaGen extends PluginBase {
	use SingletonTrait;

	protected function onLoad(): void {
		self::$instance = $this;
		$this->validateConfig();
		$this->testConnection();

		GeneratorManager::getInstance()->addGenerator(OverworldGenerator::class, GeneratorNames::OVERWORLD, fn() => null);
		GeneratorManager::getInstance()->addGenerator(NetherGenerator::class, GeneratorNames::NETHER, fn() => null);
		GeneratorManager::getInstance()->addGenerator(EndGenerator::class, GeneratorNames::END, fn() => null);

		if (Server::getInstance()->getWorldManager()->isWorldLoaded("world")) {
			Server::getInstance()->getWorldManager()->unloadWorld(Server::getInstance()->getWorldManager()->getDefaultWorld(), true);
		}
		$this->removeFolder(Server::getInstance()->getDataPath() . "worlds");
		$manager = Server::getInstance()->getWorldManager();
		$defaultOptions = new WorldCreationOptions();
		$defaultOptions->setGeneratorOptions($this->getDataFolder());
		if (!$manager->isWorldGenerated("world")) $manager->generateWorld("world", $defaultOptions->setGeneratorClass(OverworldGenerator::class));
		//if (!$manager->isWorldGenerated("nether")) $manager->generateWorld("nether", $defaultOptions->setGeneratorClass(NetherGenerator::class));
		//if (!$manager->isWorldGenerated("end")) $manager->generateWorld("end", $defaultOptions->setGeneratorClass(EndGenerator::class));
		$manager->setDefaultWorld($manager->getWorldByName("world"));
		$this->getServer()->getCommandMap()->register("JavaGen", new class extends Command {

			public function __construct() {
				parent::__construct("world");
				$this->setPermission("world.command");
			}

			public function execute(CommandSender $sender, string $commandLabel, array $args): void {
				if (!$sender instanceof Player) return;

				$world = $args[0] ?? "nether";
				$sender->teleport(Position::fromObject(new Vector3(100, 50, 0), Server::getInstance()->getWorldManager()->getWorldByName($world)));

				$sender->getNetworkSession()->sendDataPacket(GameRulesChangedPacket::create([
					"showCoordinates" => new BoolGameRule(true, false)
				]));
			}
		});
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

	private	function removeFolder(string $path): void {
		$path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		foreach (array_diff(scandir($path), [".", ".."]) as $file) {
			if (is_dir($path . $file)) {
				$this->removeFolder($path . $file);
				continue;
			}
			unlink($path . $file);
		}
		rmdir($path);
	}
}