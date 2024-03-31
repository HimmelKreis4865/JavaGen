<?php

declare(strict_types=1);

namespace JavaGen\tasks;

use JavaGen\commands\LocateCommand;
use JavaGen\helper\Dimension;
use JavaGen\stream\JavaRequests;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use RuntimeException;
use function igbinary_serialize;
use function igbinary_unserialize;

class LocateObjectTask extends AsyncTask {

	private string $serializedVector;

	public function __construct(
		private int $promiseId,
		private string $category,
		private string $name,
		private Dimension $dimension,
		Vector3 $vector3
	) {
		$this->serializedVector = igbinary_serialize($vector3) ?? throw new RuntimeException("Failed to serialize vector3 " . $vector3);
	}

	public function onRun(): void {
		$vector = igbinary_unserialize($this->serializedVector);
		if (!$vector instanceof Vector3) {
			throw new RuntimeException("Failed to deserialize vector3");
		}
		$this->setResult(JavaRequests::findNearestObject($this->category, $this->dimension, $vector, $this->name));
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
}