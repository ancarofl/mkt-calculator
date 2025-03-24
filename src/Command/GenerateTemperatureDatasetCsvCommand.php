<?php

namespace App\Command;

use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
	name: 'app:generate-temperature-dataset-csv',
	description: 'Generates a CSV file with a specified number of temperature entries (float) at specified interval length (in minutes) (DateTime).',
)]
class GenerateTemperatureDatasetCsvCommand extends Command
{
	protected function configure(): void
	{
		$this
			->addArgument('entries', InputArgument::REQUIRED, 'Number of entries (temperature-time pairs) to generate')
			->addArgument('interval', InputArgument::REQUIRED, 'Interval length in minutes');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$entries = (int) $input->getArgument('entries');
		$interval = (int) $input->getArgument('interval');
		if ($entries <= 0 || $interval <= 0) {
			$io->error('Both the number of entries and the interval length (in minutes) must be positive integers.');
			return Command::FAILURE;
		}

		$projectDir = dirname(__DIR__, 2);
		$targetDir = $projectDir . '/assets/data';
		if (!is_dir($targetDir)) {
			if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
				$io->error("Cannot create directory: $targetDir.");
				return Command::FAILURE;
			}
		}

		$timestamp = (new DateTime())->format('Y_m_d_H_i_s');
		$filename = sprintf(
			'%s/temperature_readings_%d_%dmin_%s.csv',
			$targetDir,
			$entries,
			$interval,
			$timestamp,
		);


		$file = fopen($filename, 'w');
		if (!$file) {
			$io->error("Failed to open or create file: '$filename'.");
			return Command::FAILURE;
		}

		$dateTime = new DateTime();
		for ($i = 0; $i < $entries; $i++) {
			$temperature = mt_rand(150, 300) / 10; // 15-30 degrees
			fputcsv($file, [$dateTime->format('Y-m-d H:i:s'), $temperature]);
			$dateTime->modify("+{$interval} minutes");
		}

		fclose($file);
		$io->success("CSV file '$filename' with {$entries} temperature entries at {$interval} minute intervals created successfully.");

		return Command::SUCCESS;
	}
}
