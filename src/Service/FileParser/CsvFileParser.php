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

		foreach ($csv->getRecords() as $row) {
			/* Silently skip invalid rows. 
			TODO: Keep track of count to show to user + more importantly fix the interval for the Mkt calc. Cus even if initially interval length is equal,
			skipping some rows means it's not anymore. */
			if (!$this->isValidRow($row)) {
				continue;
			}

			try {
				yield $this->parseRow($row);
			} catch (RuntimeException $e) {
				// Also silently skip the row if parsing the date failed. TODO: Also keep track of this.
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
		try {
			$time = (new DateTimeImmutable($row[0]))->format('Y-m-d H:i:s');  // it has to be this format cus of MySql
		} catch (ValueError $e) {
			throw new RuntimeException("Invalid date format: {$row[0]}. Error: {$e->getMessage()}");
		} catch (Exception $e) {
			throw new RuntimeException("Invalid date format: {$row[0]}. Error: {$e->getMessage()}");
		}

		return [
			'time' => $time,
			'temperature' => (float) $row[1],
		];
	}
}
