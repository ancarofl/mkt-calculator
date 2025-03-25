<?php

namespace App\Service;

use Doctrine\DBAL\Connection;
use Generator;
use DateTimeImmutable;

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
		$previousRecord = null;

		foreach ($records as $record) {
			if (!$this->isValidRecord($record)) {
				continue;
			}

			$this->processRecord($record, $datasetId, $batchValues, $previousRecord);
			$recordCount++;

			if ($recordCount % $this->batchSize === 0) {
				$this->flushBatch($batchValues);
			}

			$previousRecord = $record;
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

	private function processRecord(array $record, int $datasetId, array &$batchValues, ?array $previousRecord): void
	{
		$time = $record['time'];
		$temperature = $record['temperature'];

		$hoursElapsed = 0;
		if ($previousRecord !== null) {
			$previousTime = $previousRecord['time'];
			$interval = (new DateTimeImmutable($previousTime))->diff(new DateTimeImmutable($time));
			$hoursElapsed = $interval->h + ($interval->i / 60);
		}

		/* IMPORTANT! Interval during which temp was T1 = time elapsed between T1 and T2.
		So we can only start calculating the MKT when we are processing T2.
		And we calculate it for previous temp T1 and time elapsed between previous temp T1 and current temp T2.
		*/
		if ($previousRecord !== null) {
			$this->mktCalculator->update($previousRecord['temperature'], $hoursElapsed);
		}

		$batchValues[] = [
			'dataset_id' => $datasetId,
			'time' => $time,
			'temperature' => $temperature,
			'hours_elapsed' => $hoursElapsed,
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
		$sql = 'INSERT INTO temperature_records (dataset_id, time, temperature, hours_elapsed) VALUES ';
		$placeholders = '';

		// (:dataset_id0, :time0, :temperature0, :hours_elapsed0)  etc
		foreach ($batchValues as $index => $value) {
			if ($index > 0) {
				$placeholders .= ', ';
			}
			$placeholders .= "(:dataset_id{$index}, :time{$index}, :temperature{$index}, :hours_elapsed{$index})";
		}

		return $sql . $placeholders;
	}

	private function bindValues($statement, array $batchValues): void
	{
		foreach ($batchValues as $index => $value) {
			$statement->bindValue(':dataset_id' . $index, $value['dataset_id']);
			$statement->bindValue(':time' . $index, $value['time']);
			$statement->bindValue(':temperature' . $index, $value['temperature']);
			$statement->bindValue(':hours_elapsed' . $index, $value['hours_elapsed']);
		}
	}
}
