<?php

declare(strict_types=1);

namespace JavaGen\helper\block;

use pocketmine\block\VanillaBlocks;

final class BlockPalette {

	private int $stateAir;

	/** @var string[] $rawBlockData */
	private array $rawBlockData;

	/** @var int[] $map */
	public array $map = [];

	public function __construct(array $rawMap) {
		$this->stateAir = VanillaBlocks::AIR()->getStateId();
		$this->parseMap($rawMap);
	}

	public function getBlockFromIndex(int $index): int {
		return $this->map[$index] ?? $this->stateAir;
	}

	/**
	 * @param string[] $map
	 */
	private function parseMap(array $map): void {
		foreach ($map as $i => $blockData) {
			$block = JavaToBedrockBlockData::getInstance()->javaToBedrockBlock($blockData);
			$this->map[$i] = $block->getStateId();
		}
		$this->rawBlockData = $map;
	}

	public function getBlockData(int $index): string {
		return $this->rawBlockData[$index];
	}
}