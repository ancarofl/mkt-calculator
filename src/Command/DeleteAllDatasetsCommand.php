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
	description: 'Delete all temperature data sets and their records.'
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

		$io->note(sprintf('Deleting all data sets and their records...'));

		$batchSize = 5;
		$i = 0;

		foreach ($datasets as $dataset) {
			$this->em->remove($dataset);

			$i++;
			$isBatchCompleted = $i % $batchSize === 0;

			if ($isBatchCompleted) {
				$this->em->flush();
				$this->em->clear();
			}
		}

		$this->em->flush();
		$this->em->clear();

		$io->success('All data sets and their records have been deleted successfully.');

		return Command::SUCCESS;
	}
}
