<?php

declare(strict_types=1);

namespace JavaGen\helper\biome;

class BiomePalette {

	/** @var int[] $biomeIds */
	private array $biomeIds = [];

	/**
	 * @param string[] $biomeStrings
	 */
	public function __construct(array $biomeStrings, private int $defaultBiomeId) {
		foreach	($biomeStrings as $identifier) {
			$this->biomeIds[] = BiomeIdentifierRegistry::getInstance()->get($identifier);
		}
	}

	public function get(int $index): int {
		return $this->biomeIds[$index] ?? $this->defaultBiomeId;
	}
}