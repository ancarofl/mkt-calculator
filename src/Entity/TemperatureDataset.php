<?php

namespace App\Entity;

use App\Repository\TemperatureDatasetRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: TemperatureDatasetRepository::class)]
#[ORM\Table(name: 'temperature_datasets')] // Doctrine defaults to singular but is flexible, I prefer plural
#[ORM\HasLifecycleCallbacks]
class TemperatureDataset
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private int $id;

	#[ORM\Column(type: 'string', length: 255)]
	private string $name;

	#[ORM\Column(type: 'float', name: 'calculated_mkt', nullable: true)]
	private ?float $calculatedMkt = null;

	#[ORM\Column(
		name: 'created_at',
		type: Types::DATETIME_IMMUTABLE
	)]
	private DateTimeImmutable $createdAt;

	#[ORM\Column(
		name: 'updated_at',
		type: Types::DATETIME_IMMUTABLE
	)]
	private DateTimeImmutable $updatedAt;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	#[ORM\PrePersist]
	public function onPrePersist(): void
	{
		$now = new DateTimeImmutable();
		$this->createdAt = $now;
		$this->updatedAt = $now;
	}

	#[ORM\PreUpdate]
	public function onPreUpdate(): void
	{
		$this->updatedAt = new DateTimeImmutable();
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	public function getCalculatedMkt(): ?float
	{
		return $this->calculatedMkt;
	}

	public function setCalculatedMkt(float $calculatedMkt): self
	{
		$this->calculatedMkt = $calculatedMkt;
		return $this;
	}

	public function getCreatedAt(): \DateTimeInterface
	{
		return $this->createdAt;
	}

	public function getUpdatedAt(): \DateTimeInterface
	{
		return $this->updatedAt;
	}
}
