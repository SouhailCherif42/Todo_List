<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114141103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE task_owners (task_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_79DB16298DB60186 (task_id), INDEX IDX_79DB1629A76ED395 (user_id), PRIMARY KEY(task_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task_assignees (task_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6DEED38D8DB60186 (task_id), INDEX IDX_6DEED38DA76ED395 (user_id), PRIMARY KEY(task_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task_owners ADD CONSTRAINT FK_79DB16298DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_owners ADD CONSTRAINT FK_79DB1629A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_assignees ADD CONSTRAINT FK_6DEED38D8DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task_assignees ADD CONSTRAINT FK_6DEED38DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD status VARCHAR(255) DEFAULT NULL, ADD priority INT DEFAULT NULL, ADD category VARCHAR(255) DEFAULT NULL, DROP owner_id');
        $this->addSql('ALTER TABLE user DROP roles');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task_owners DROP FOREIGN KEY FK_79DB16298DB60186');
        $this->addSql('ALTER TABLE task_owners DROP FOREIGN KEY FK_79DB1629A76ED395');
        $this->addSql('ALTER TABLE task_assignees DROP FOREIGN KEY FK_6DEED38D8DB60186');
        $this->addSql('ALTER TABLE task_assignees DROP FOREIGN KEY FK_6DEED38DA76ED395');
        $this->addSql('DROP TABLE task_owners');
        $this->addSql('DROP TABLE task_assignees');
        $this->addSql('ALTER TABLE task ADD owner_id INT NOT NULL, DROP status, DROP priority, DROP category');
        $this->addSql('ALTER TABLE user ADD roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
