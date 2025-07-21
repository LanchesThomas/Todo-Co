<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625081911 extends AbstractMigration
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
     * This up() migration is auto-generated, please modify it to your needs
     */
    public function up(Schema $schema): void
    {
        // This up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE task ADD user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD roles JSON NOT NULL
        SQL);
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
        $this->addSql(<<<'SQL'
            ALTER TABLE task DROP CONSTRAINT FK_527EDB25A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_527EDB25A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE task DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP roles
        SQL);
    }
}
