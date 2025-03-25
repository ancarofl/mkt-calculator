<?php

namespace App\Command;

use App\Repository\TemperatureDatasetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Service\Attribute\Required;

#[AsCommand(
	name: 'app:delete-all-datasets',
	description: 'Delete all temperature datasets and their records and reset autoincrement indexes.'
)]
class DeleteAllDatasetsCommand extends Command
{
	#[Required]
	public EntityManagerInterface $em;

	#[Required]
	public TemperatureDatasetRepository $datasetRepository;

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$datasets = $this->datasetRepository->findAll();

		if (empty($datasets)) {
			$io->warning('No datasets to delete.');
			return Command::SUCCESS;
		}

		$io->note('Deleting all datasets and their records...');

		$batchSize = 5;
		$i = 0;

		foreach ($datasets as $dataset) {
			$this->em->remove($dataset);

			$i++;
			if ($i % $batchSize === 0) {
				$this->em->flush();
				$this->em->clear();
			}
		}

		$this->em->flush();
		$this->em->clear();

		$io->note('Resetting autoincrement indexes...');

		$connection = $this->em->getConnection();
		$schemaManager = $connection->createSchemaManager();
		$tables = $schemaManager->listTableNames();

		foreach ($tables as $table) {
			$connection->executeStatement("ALTER TABLE `$table` AUTO_INCREMENT = 1");
		}

		$io->success('All datasets deleted, and autoincrement indexes reset successfully.');

		return Command::SUCCESS;
	}
}
