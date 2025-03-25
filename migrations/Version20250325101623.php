<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250325101623 extends AbstractMigration
{
	public function getDescription(): string
	{
		return '';
	}

	public function up(Schema $schema): void
	{
		$this->addSql('ALTER TABLE temperature_records ADD hours_elapsed FLOAT DEFAULT 0');
	}

	public function down(Schema $schema): void
	{
		$this->addSql('ALTER TABLE temperature_records DROP COLUMN hours_elapsed');
	}
}
