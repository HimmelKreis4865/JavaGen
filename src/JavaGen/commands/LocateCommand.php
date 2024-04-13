<?php

declare(strict_types=1);

namespace JavaGen\commands;

use JavaGen\data\MessageKey;
use JavaGen\data\Messages;
use JavaGen\helper\biome\BiomeIdentifierRegistry;
use JavaGen\helper\GeneratorNames;
use JavaGen\structure\StructureType;
use JavaGen\tasks\LocateObjectTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\promise\Promise;
use pocketmine\promise\PromiseResolver;
use pocketmine\Server;
use pocketmine\world\Position;
use function array_map;
use function floor;
use function implode;
use function spl_object_id;

final class LocateCommand extends Command {

	/**
	 * @var PromiseResolver[] $resolverCache
	 * @phpstan-var array<int, PromiseResolver<Vector3>> $resolverCache
	 */
	public static array $resolverCache = [];

	public function __construct() {
		parent::__construct(
			"locate",
			"Locates the nearest structure or biome",
			"/locate list <structure|biome> " .
			"OR /locate biome <" . implode("|", BiomeIdentifierRegistry::getInstance()->getBiomeNames()) . "> " .
			"OR /locate structure <" . implode("|", array_map(fn(StructureType $type) => $type->value, StructureType::cases())) . ">");
		$this->setPermission("locate.command");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void {
		if (!$sender instanceof Player) {
			$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_INGAME));
			return;
		}
		if (!isset($args[1])) {
			$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_USAGE));
			return;
		}
		switch ($args[0]) {
			case "biome":
				$targetBiome = $args[1];
				if (BiomeIdentifierRegistry::getInstance()->get($targetBiome, false) === null) {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_INVALID_BIOME, $targetBiome));
					return;
				}
				$this->locateObject("biome", $targetBiome, $sender->getPosition())->onCompletion(function(mixed $vector3) use ($sender, $targetBiome): void {
					if ($vector3 instanceof Vector3) {
						$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_SUCCESS_BIOME, $targetBiome, $vector3->x, "?", $vector3->z, floor($sender->getPosition()->distance($vector3))));
					}
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
				$this->locateObject("structure", $targetStructure, $sender->getPosition())->onCompletion(function(mixed $vector3) use ($sender, $targetStructure): void {
					if ($vector3 instanceof Vector3) {
						$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_SUCCESS_STRUCTURE, $targetStructure, $vector3->x, "?", $vector3->z, floor($sender->getPosition()->distance($vector3))));
					}
					}, function() use ($sender, $targetStructure): void {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_NOTHING_STRUCTURE, $targetStructure));
				});
				break;
			case "list":
				$type = $args[1];
				if ($type !== "structure" and $type !== "biome") {
					$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_INVALID_CATEGORY, $type));
					return;
				}
				$array = ($type === "structure"
					? array_map(fn(string $name) => Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_LIST_ELEMENT, $name), BiomeIdentifierRegistry::getInstance()->getBiomeNames())
					: array_map(fn(StructureType $type) => Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_LIST_ELEMENT, $type->value), StructureType::cases())
				);
				$elements = implode(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_LIST_SEPARATOR), $array);
				$sender->sendMessage(Messages::getInstance()->get(($type === "structure" ? MessageKey::COMMAND_LOCATE_LIST_STRUCTURE : MessageKey::COMMAND_LOCATE_LIST_BIOME)) . $elements);
				break;
			default:
				$sender->sendMessage(Messages::getInstance()->get(MessageKey::COMMAND_LOCATE_USAGE));
				break;
		}
	}

	/**
	 * @phpstan-return Promise<Vector3
	 */
	private function locateObject(string $category, string $name, Position $position): Promise {
		$resolver = new PromiseResolver();
		$id = spl_object_id($resolver);
		LocateCommand::$resolverCache[$id] = $resolver;
		$dimension = GeneratorNames::toDimension($position->getWorld());
		$vec = $position->asVector3()->floor();

		Server::getInstance()->getAsyncPool()->submitTask(new LocateObjectTask($id, $category, $name, $dimension, $vec));
		return $resolver->getPromise();
	}
}