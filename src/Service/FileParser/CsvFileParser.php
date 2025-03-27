<?php

namespace App\Service\FileParser;

use League\Csv\Reader;
use League\Csv\Exception as CsvException;
use League\Csv\InvalidArgument;
use League\Csv\SyntaxError;
use DateTimeImmutable;
use RuntimeException;
use Generator;
use Exception;
use ValueError;

class CsvFileParser implements FileParserInterface
{
	public function parse(string $filePath): Generator
	{
		try {
			$csv = Reader::createFromPath($filePath, 'r');
		} catch (CsvException | InvalidArgument | SyntaxError $e) {
			throw new RuntimeException("CSV error: " . $e->getMessage());
		}

		// Silently skip invalid rows. Calculated MKT is unaffected.
		foreach ($csv->getRecords() as $row) {
			if (!$this->isValidRow($row)) {
				continue;
			}

			try {
				yield $this->parseRow($row);
			} catch (RuntimeException $e) {
				continue;
			}
		}
	}

	private function isValidRow(array $row): bool
	{
		return count($row) === 2
			&& !empty($row[0])
			&& is_numeric($row[1]);
	}

	private function parseRow(array $row): array
	{
		$rawTime = $row[0];

		if (is_numeric($rawTime)) {
			$time = date('Y-m-d H:i:s', (int) $rawTime);
		} else {
			try {
				$time = (new DateTimeImmutable($rawTime))->format('Y-m-d H:i:s');
			} catch (ValueError | Exception $e) {
				throw new RuntimeException("Invalid date format: {$rawTime}. Error: {$e->getMessage()}");
			}
		}

		return [
			'time' => $time,
			'temperature' => (float) $row[1],
		];
	}
}
