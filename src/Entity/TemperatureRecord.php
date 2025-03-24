<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Repository\TemperatureRecordRepository;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: TemperatureRecordRepository::class)]
#[ORM\Table(name: 'temperature_records')]
class TemperatureRecord
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private int $id;

	#[ORM\ManyToOne(targetEntity: TemperatureDataset::class)]
	#[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
	private TemperatureDataset $dataset;

	#[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
	private DateTimeImmutable $time;

	#[ORM\Column(type: 'float')]
	private float $temperature;

	public function __construct(TemperatureDataset $dataset, DateTimeImmutable $time, float $temperature)
	{
		$this->dataset = $dataset;
		$this->time = $time;
		$this->temperature = $temperature;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getDataset(): TemperatureDataset
	{
		return $this->dataset;
	}

	public function getTime(): \DateTimeInterface
	{
		return $this->time;
	}

	public function getTemperature(): float
	{
		return $this->temperature;
	}
}
