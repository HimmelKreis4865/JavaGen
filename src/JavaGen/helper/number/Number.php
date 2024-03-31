<?php

declare(strict_types=1);

namespace JavaGen\helper\number;

use function is_array;

abstract class Number {

	/**
	 * Returns the number at current state, this may be different for every execution
	 */
	abstract public function getNumber(): int|float;

	public static function exact(int|float $number): Number {
		return new ExactNumber($number);
	}

	public static function random(int|float $min, int|float $max): Number {
		return new RandomNumber($min, $max);
	}

	/**
	 * @param array<string, int|float>|int|float $json
	 */
	public static function fromJson(array|int|float $json): Number {
		return is_array($json) ? self::random($json["min"], $json["max"]) : self::exact($json);
	}
}