<?php

declare(strict_types=1);

namespace JavaGen\generator;

use GlobalLogger;
use JavaGen\helper\biome\BiomePalette;
use JavaGen\helper\block\BlockPalette;
use JavaGen\helper\Dimension;
use JavaGen\stream\JavaRequests;
use JavaGen\structure\StructureType;
use pocketmine\block\Block;
use pocketmine\data\bedrock\BiomeIds;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\AsyncWorker;
use pocketmine\thread\Thread;
use pocketmine\utils\BinaryStream;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\PalettedBlockArray;
use pocketmine\world\format\SubChunk;
use pocketmine\world\generator\Generator;
use RuntimeException;
use function array_fill;
use function igbinary_serialize;
use function json_decode;
use function json_encode;
use function microtime;
use function str_replace;
use function var_dump;

abstract class BaseJavaGenerator extends Generator {

	protected const MIN_Y = 0;

	protected const MAX_Y = 256;

	protected const DEFAULT_BIOME = BiomeIds::PLAINS;

	public const WORKER_DATA = "javagen:post_gen_data";

	public function __construct(int $seed, string $preset) {
		parent::__construct($seed, $preset);
	}

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunk === null) {
			return;
		}
		$stream = JavaRequests::requestChunk($this->getDimension(), $chunkX, $chunkZ);

		$minIndex = (static::MIN_Y >> 4);
		$maxIndex = ((static::MAX_Y - 1) >> 4);
		for ($i = $minIndex; $i <= $maxIndex; $i++) {
			$chunk->setSubChunk($i, self::readSubChunk($stream));
		}

		$saveData = [
			"chunkX" => $chunkX,
			"chunkZ" => $chunkZ,
			"dimension" => $this->getDimension()->value
		];

		$tileCount = $stream->getInt();
		if ($tileCount > 0) {
			for ($i = 0; $i < $tileCount; $i++) {
				$tileData = $stream->get($stream->getUnsignedVarInt());
				$saveData["tiles"][] = json_decode($tileData, true);
			}
		}

		$structureCount = $stream->getInt();
		for ($i = 0; $i < $structureCount; $i++) {
			$boundingBox = $stream->get($stream->getUnsignedVarInt());
			$name = str_replace("minecraft:", "", $stream->get($stream->getUnsignedVarInt()));
			$structureType = StructureType::tryFrom($name);
			if ($structureType === null) {
				GlobalLogger::get()->debug("Structure type for name " . $name . " not found");
				continue;
			}
			$saveData["structures"][] = [
				"name" => $name,
				"boundingBox" => $boundingBox
			];
		}
		if (isset($saveData["tiles"]) || isset($saveData["structures"])) {
			$this->mergeWorkerData($saveData);
		}
	}

	/**
	 * @param array $data
	 * @phpstan-param array<string, scalar|array> $data
	 */
	public function mergeWorkerData(array $data): void {
		$worker = Thread::getCurrentThread();
		if ($worker instanceof AsyncWorker) {
			$existing = $worker->getFromThreadStore(self::WORKER_DATA);
			if (is_string($existing)) {
				$existing = json_decode($existing, true);
				$existing[] = $data;
				$data = $existing;
			} else {
				$data = [$data];
			}
			$encodedData = json_encode($data);
			if ($encodedData === false) {
				throw new RuntimeException("Failed to encode JSON array: " . igbinary_serialize($data));
			}
			$worker->saveToThreadStore(self::WORKER_DATA, $encodedData);
		} else {
			throw new RuntimeException("Did not expect to be in a thread of type " . ($worker === null ? "NULL" : $worker::class) . " instead of " . AsyncWorker::class);
		}
	}

	public static function readSubChunk(BinaryStream $stream): SubChunk {
		$subChunk = new SubChunk(
			Block::EMPTY_STATE_ID,
			[new PalettedBlockArray(Block::EMPTY_STATE_ID)],
			new PalettedBlockArray(static::DEFAULT_BIOME)
		);
		$empty = $stream->getByte();
		if ($empty === 1) {
			return $subChunk;
		}

		$bitsPerBlock = $stream->getByte();
		if ($bitsPerBlock > 0) {
			$paletteSize = $stream->getInt();
			$stateIdMap = [];
			for ($j = 0; $j < $paletteSize; $j++) {
				$stateIdMap[] = $stream->get($stream->getUnsignedVarInt());
			}
			$blockPalette = new BlockPalette($stateIdMap);
			$blockStorageSize = $stream->getInt();
			$blockData = [];
			for ($j = 0; $j < $blockStorageSize; $j++) {
				$blockData[] = $blockPalette->getBlockFromIndex($stream->getInt());
			}
		} else {
			$blockPalette = new BlockPalette([$stream->get($stream->getUnsignedVarInt())]);
			$blockData = array_fill(0, 4096, $blockPalette->getBlockFromIndex(0));
		}

		$bitsPerBiome = $stream->getByte();
		if ($bitsPerBiome > 0) {
			$biomePaletteSize = $stream->getInt();
			$biomes = [];
			for ($j = 0; $j < $biomePaletteSize; $j++) {
				$biomes[] = $stream->get($stream->getUnsignedVarInt());
			}
			$biomePalette = new BiomePalette($biomes, static::DEFAULT_BIOME);

			$biomeStorageSize = $stream->getInt();
			$biomeData = [];
			for ($i = 0; $i < $biomeStorageSize; $i++) {
				$biomeData[] = $stream->getByte();
			}
		} else {
			$biomePalette = new BiomePalette([$stream->get($stream->getUnsignedVarInt())], static::DEFAULT_BIOME);
			$biomeData = array_fill(0, 64, 0);
		}

		$blockOffset = 0;
		for ($y = 0; $y < 16; $y++) {
			for ($z = 0; $z < 16; $z++) {
				$biomeZ = $z >> 2;
				for ($x = 0; $x < 16; $x++) {
					$biomeX = $x >> 2;
					$subChunk->setBlockStateId($x, $y, $z, $blockData[$blockOffset++]);
					$biomeOffset = ((($y | $biomeZ) << 2) | $biomeX);
					$subChunk->getBiomeArray()->set($x, $y, $z, $biomePalette->get($biomeData[$biomeOffset]));
				}
			}
		}
		return $subChunk;
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {}

	abstract public function getDimension(): Dimension;
}