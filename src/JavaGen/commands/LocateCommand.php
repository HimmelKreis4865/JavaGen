<?php

declare(strict_types=1);

namespace JavaGen\commands;

use JavaGen\data\MessageKey;
use JavaGen\data\Messages;
use JavaGen\helper\biome\BiomeIdentifierRegistry;
use JavaGen\helper\Dimension;
use JavaGen\helper\GeneratorNames;
use JavaGen\stream\JavaRequests;
use JavaGen\structure\StructureType;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\world\Position;
use function array_map;
use function floor;
use function implode;
use function spl_object_id;

final class LocateCommand extends Command {

	/**
	 * @var PromiseResolver[]
	 * @phpstan-var array<int, PromiseResolver>
	 */
	public static array $resolverCache = [];

	public function __construct() {
		parent::__construct(
			"locate",
			"Locates the nearest structure or biome",
			"/locate biome <" . implode("|", BiomeIdentifierRegistry::getInstance()->getBiomeNames()) . "> " .
			"OR /locate structure <" . implode("|", array_map(fn(StructureType $type) => $type->value, StructureType::cases())) . ">");
		$this->setPermission("locate.command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		if (!$sender instanceof Player or !isset($args[1])) {
			$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_INGAME));
			return;
		}
		switch ($args[0]) {
			case "biome":
				$targetBiome = $args[1];
				if (BiomeIdentifierRegistry::getInstance()->get($targetBiome, false) === null) {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_INVALID_BIOME, $targetBiome));
					return;
				}
				$this->locateObject("biome", $targetBiome, $sender->getPosition())->onCompletion(function(Vector3 $vector3) use ($sender, $targetBiome): void {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_SUCCESS_BIOME, $targetBiome, $vector3->x, "?", $vector3->z, floor($sender->getPosition()->distance($vector3))));
				}, function() use ($sender, $targetBiome): void {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_NOTHING_BIOME, $targetBiome));
				});
				break;
			case "structure":
				$targetStructure = $args[1];
				if (StructureType::tryFrom($targetStructure) === null) {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_INVALID_STRUCTURE, $targetStructure));
					return;
				}
				$this->locateObject("structure", $targetStructure, $sender->getPosition())->onCompletion(function(Vector3 $vector3) use ($sender, $targetStructure): void {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_SUCCESS_STRUCTURE, $targetStructure, $vector3->x, "?", $vector3->z, floor($sender->getPosition()->distance($vector3))));
				}, function() use ($sender, $targetStructure): void {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_NOTHING_STRUCTURE, $targetStructure));
				});
				break;
			default:
				$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_INVALID_CATEGORY, $args[0]));
				break;
		}
	}

	private function locateObject(string $category, string $name, Position $position): Promise {
		$resolver = new PromiseResolver();
		$id = spl_object_id($resolver);
		LocateCommand::$resolverCache[$id] = $resolver;
		$dimension = GeneratorNames::toDimension($position->getWorld());
		$vec = $position->asVector3()->floor();

		$task = new class ($id, $category, $name, $dimension, $vec->x, $vec->y, $vec->z) extends AsyncTask {

			public function __construct(
				private readonly int $promiseId,
				private readonly string $category,
				private readonly string $name,
				private readonly Dimension $dimension,
				private readonly int $x,
				private readonly int $y,
				private readonly int $z,
			) { }

			public function onRun(): void {
				$this->setResult(JavaRequests::findNearestObject($this->category, $this->dimension, new Vector3($this->x, $this->y, $this->z), $this->name));
			}

			public function onCompletion(): void {
				$resolver = LocateCommand::$resolverCache[$this->promiseId] ?? null;
				unset(LocateCommand::$resolverCache[$this->promiseId]);
				if ($resolver === null) {
					return;
				}
				if ($this->hasResult() and ($result = $this->getResult()) instanceof Vector3) {
					$resolver->resolve($result);
				} else {
					$resolver->reject();
				}
			}
		};
		Server::getInstance()->getAsyncPool()->submitTask($task);
		return $resolver->getPromise();
	}
}