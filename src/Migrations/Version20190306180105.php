<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190306180105 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE github_repo (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, owner VARCHAR(255) NOT NULL, uri VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE github_repo_email_address (github_repo_id INT NOT NULL, email_address_id INT NOT NULL, INDEX IDX_4B9EAA33B23C03A9 (github_repo_id), INDEX IDX_4B9EAA3359045DAA (email_address_id), PRIMARY KEY(github_repo_id, email_address_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email_address (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, latest_version VARCHAR(32) NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE github_repo_email_address ADD CONSTRAINT FK_4B9EAA33B23C03A9 FOREIGN KEY (github_repo_id) REFERENCES github_repo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE github_repo_email_address ADD CONSTRAINT FK_4B9EAA3359045DAA FOREIGN KEY (email_address_id) REFERENCES email_address (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE github_repo_email_address DROP FOREIGN KEY FK_4B9EAA33B23C03A9');
        $this->addSql('ALTER TABLE github_repo_email_address DROP FOREIGN KEY FK_4B9EAA3359045DAA');
        $this->addSql('DROP TABLE github_repo');
        $this->addSql('DROP TABLE github_repo_email_address');
        $this->addSql('DROP TABLE email_address');
        $this->addSql('DROP TABLE package');
    }
}
