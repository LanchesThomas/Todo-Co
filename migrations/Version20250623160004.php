<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250623160004 extends AbstractMigration
{
    /**
     * Returns the description of the migration.
     *
     * This method provides a brief description of what this migration does.
     *
     * @return string The description of the migration.
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * Applies the migration to the database schema.
     *
     * This method is called when the migration is applied.
     *
     * @param Schema $schema The schema to apply the migration to.
     */
    public function up(Schema $schema): void
    {
        // This up() migration is auto-generated, please modify it to your needs

    }

    /**
     * Reverts the migration from the database schema.
     *
     * This method is called when the migration is rolled back.
     *
     * @param Schema $schema The schema to revert the migration from.
     */
    public function down(Schema $schema): void
    {
        // This down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
    }
}
