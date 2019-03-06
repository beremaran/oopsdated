<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190307210930 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE configuration_file (id INT AUTO_INCREMENT NOT NULL, repository_id INT NOT NULL, package_manager_type VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_E9CC401750C9D4F7 (repository_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE configuration_file ADD CONSTRAINT FK_E9CC401750C9D4F7 FOREIGN KEY (repository_id) REFERENCES github_repo (id)');
        $this->addSql('ALTER TABLE github_repo DROP npm_configuration, DROP composer_configuration');
        $this->addSql('ALTER TABLE package ADD registry_type VARCHAR(255) NOT NULL, CHANGE latest_version version VARCHAR(32) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE configuration_file');
        $this->addSql('ALTER TABLE github_repo ADD npm_configuration LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD composer_configuration LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE package DROP registry_type, CHANGE version latest_version VARCHAR(32) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
