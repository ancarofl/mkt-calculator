<?php

namespace App\Repository;

use App\Entity\TemperatureDataset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TemperatureDataset>
 */
class TemperatureDatasetRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, TemperatureDataset::class);
	}

	public function save(TemperatureDataset $dataset, bool $flush = false): void
	{
		$this->getEntityManager()->persist($dataset);
		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}
}
