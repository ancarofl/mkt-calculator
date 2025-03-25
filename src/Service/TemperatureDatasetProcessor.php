<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Generator;

class TemperatureDatasetProcessor
{
	private Connection $connection;
	private MktCalculator $mktCalculator;
	private int $batchSize;

	public function __construct(Connection $connection, MktCalculator $mktCalculator, int $batchSize = 500)
	{
		$this->connection = $connection;
		$this->mktCalculator = $mktCalculator;
		$this->batchSize = $batchSize;
	}

	public function processDataset(int $datasetId, Generator $records): array
	{
		$recordCount = 0;
		$batchValues = [];

		foreach ($records as $record) {
			if ($this->isValidRecord($record)) {
				$this->processRecord($record, $datasetId, $batchValues);
				$recordCount++;

				if ($recordCount % $this->batchSize === 0) {
					$this->flushBatch($batchValues);
				}
			}
		}

		if (!empty($batchValues)) {
			$this->flushBatch($batchValues);
		}

		return [
			'recordCount' => $recordCount,
			'mkt' => $this->mktCalculator->calculate(),
		];
	}

	private function isValidRecord(array $record): bool
	{
		return isset($record['time'], $record['temperature']);
	}

	private function processRecord(array $record, int $datasetId, array &$batchValues): void
	{
		$time = $record['time'];
		$temperature = $record['temperature'];

		$this->mktCalculator->update($temperature);

		$batchValues[] = [
			'dataset_id' => $datasetId,
			'time' => $time,
			'temperature' => $temperature
		];
	}

	private function flushBatch(array &$batchValues): void
	{
		if (empty($batchValues)) {
			return;
		}

		$sql = $this->buildInsertQuery($batchValues);
		$statement = $this->connection->prepare($sql);
		$this->bindValues($statement, $batchValues);
		$statement->executeStatement();

		$batchValues = [];
		gc_collect_cycles(); // Garbage collection. TODO: Should I use this?
	}

	private function buildInsertQuery(array $batchValues): string
	{
		$sql = 'INSERT INTO temperature_records (dataset_id, time, temperature) VALUES ';
		$placeholders = '';

		// (:dataset_id0, :time0, :temperature0)  etc
		foreach ($batchValues as $index => $value) {
			if ($index > 0) {
				$placeholders .= ', ';
			}
			$placeholders .= "(:dataset_id{$index}, :time{$index}, :temperature{$index})";
		}

		return $sql . $placeholders;
	}

	// Protects against SQL injection. Binding ensures that user input is treated as data rather than executable code.
	private function bindValues($statement, array $batchValues): void
	{
		foreach ($batchValues as $index => $value) {
			$statement->bindValue(':dataset_id' . $index, $value['dataset_id']);
			$statement->bindValue(':time' . $index, $value['time']);
			$statement->bindValue(':temperature' . $index, $value['temperature']);
		}
	}
}
