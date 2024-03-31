<?php

declare(strict_types=1);

namespace JavaGen\helper\number;

use function is_float;
use function mt_rand;

class RandomNumber extends Number {

	public function __construct(private readonly int|float $min, private readonly int|float $max) {}

	public function getNumber(): int|float {
		if (is_float($this->min) || is_float($this->max)) {
			$factor = $this->max * (1 << 28);
			return mt_rand((int) ($this->min * $factor), (int) ($this->max * $factor)) / $factor;
		}
		return mt_rand($this->min, $this->max);
	}
}