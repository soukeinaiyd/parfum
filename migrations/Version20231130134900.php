<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231130134900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresses (id INT AUTO_INCREMENT NOT NULL, rue VARCHAR(255) NOT NULL, ville VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD adresses_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64985E14726 FOREIGN KEY (adresses_id) REFERENCES adresses (id)');
        $this->addSql('CREATE INDEX IDX_8D93D64985E14726 ON user (adresses_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985E14726');
        $this->addSql('DROP TABLE adresses');
        $this->addSql('DROP INDEX IDX_8D93D64985E14726 ON user');
        $this->addSql('ALTER TABLE user DROP adresses_id');
    }
}
