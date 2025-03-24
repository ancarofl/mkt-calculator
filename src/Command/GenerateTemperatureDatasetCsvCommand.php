<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use DateTime;

#[AsCommand(
	name: 'app:generate-temperature-dataset-csv',
	description: 'Generates a CSV file with 300 temperatures (float) at 5 minute intervals (DateTime).',
)]
class GenerateTemperatureDatasetCsvCommand extends Command
{
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$timestamp = (new DateTime())->format('Y-m-d_H-i-s');
		$filename = "temperature_readings_{$timestamp}.csv";

		$file = fopen($filename, 'w');

		if (!$file) {
			$io->error("Failed to open or create '$filename'.");
			return Command::FAILURE;
		}

		$dateTime = new DateTime('2025-03-24 00:00:00');

		for ($i = 0; $i < 300; $i++) {
			$temperature = mt_rand(150, 300) / 10; // 15-30 degrees
			fputcsv($file, [$dateTime->format('Y-m-d H:i:s'), $temperature]);
			$dateTime->modify('+5 minutes');
		}

		fclose($file);
		$io->success("CSV file '$filename' with 300 temperatures at 5 minute intervals created successfully.");

		return Command::SUCCESS;
	}
}
