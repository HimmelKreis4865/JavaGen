<?php

declare(strict_types=1);

namespace JavaGen\stream;

use JavaGen\helper\Dimension;
use JavaGen\helper\GeneratorNames;
use pocketmine\math\Vector3;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Internet;
use pocketmine\world\Position;
use RuntimeException;
use function json_decode;

final class JavaRequests {

	public static function requestChunk(Dimension $dimension, int $chunkX, int $chunkZ, &$stream): void {
		$fullResponse = self::request("http://localhost:8000/chunkRequest?chunkX=$chunkX&chunkZ=$chunkZ&dimension=" . $dimension->value);
		$stream = new BinaryStream($fullResponse);
	}

	public static function findNearestBiome(Position $position, string $biomeName): ?Vector3 {
		$dimension = GeneratorNames::toDimension($position->getWorld());
		return self::findNearestObject("biome", $dimension, $position, $biomeName);
	}

	public static function findNearestStructure(Position $position, string $structureName): ?Vector3 {
		$dimension = GeneratorNames::toDimension($position->getWorld());
		return self::findNearestObject("structure", $dimension, $position, $structureName);
	}

	public static function findNearestObject(string $type, Dimension $dimension, Vector3 $position, string $objectName): ?Vector3 {
		$position = $position->floor();
		$fullResponse = self::request("http://localhost:8000/locate?category=$type&type=$objectName&x=" . $position->x . "&y=" . $position->y . "&z=" . $position->z . "&dimension=" . $dimension->value);

		$json = json_decode($fullResponse, true);
		if ($json === null or !isset($json["x"]) or !isset($json["y"]) or !isset($json["z"])) {
			return null;
		}
		return new Vector3($json["x"], $json["y"], $json["z"]);
	}

	private static function request(string $url): string {
		$result = Internet::getURL($url, 3);
		if ($result === null) {
			throw new RuntimeException("Connection to the upstream Java Server lost! Check the status of the server");
		}
		return $result->getBody();
	}
}