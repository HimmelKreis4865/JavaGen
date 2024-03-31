<?php

declare(strict_types=1);

namespace JavaGen\helper\number;

class ExactNumber extends Number {

	public function __construct(private int|float $number) {}

	public function getNumber(): int|float {
		return $this->number;
	}
}