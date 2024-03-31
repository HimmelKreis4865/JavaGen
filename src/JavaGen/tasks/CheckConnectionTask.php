<?php

declare(strict_types=1);

namespace JavaGen\tasks;

use JavaGen\helper\Dimension;
use JavaGen\stream\JavaRequests;
use pocketmine\scheduler\AsyncTask;

final class CheckConnectionTask extends AsyncTask {

	public function onRun(): void {
		JavaRequests::requestChunk(Dimension::OVERWORLD, 0, 0);
	}
}