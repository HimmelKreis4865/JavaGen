<?php

declare(strict_types=1);

namespace JavaGen\tile;

use InvalidArgumentException;
use pocketmine\math\Vector3;

class JavaTile {

	/** @var array<int, string|int> $additionalData */
	private array $additionalData;

	private Vector3 $position;

	private string $id;

	public function __construct(array $data) {
		if (!isset($data["id"]) or !isset($data["x"]) or !isset($data["y"]) or !isset($data["z"])) {
			throw new InvalidArgumentException("Tile does not contain important information");
		}
		$this->position = new Vector3($data["x"], $data["y"], $data["z"]);
		$this->id = $data["id"];
		$this->additionalData = $data;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getPosition(): Vector3 {
		return $this->position;
	}

	public function getAdditionalData(): array {
		return $this->additionalData;
	}
}