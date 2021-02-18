<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210218180756 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_admin DROP FOREIGN KEY FK_9B5B04E8166D1F9C');
        $this->addSql('ALTER TABLE project_admin DROP FOREIGN KEY FK_9B5B04E8A76ED395');
        $this->addSql('ALTER TABLE project_admin ADD CONSTRAINT FK_9B5B04E8166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_admin ADD CONSTRAINT FK_9B5B04E8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_admin DROP FOREIGN KEY FK_9B5B04E8A76ED395');
        $this->addSql('ALTER TABLE project_admin DROP FOREIGN KEY FK_9B5B04E8166D1F9C');
        $this->addSql('ALTER TABLE project_admin ADD CONSTRAINT FK_9B5B04E8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE project_admin ADD CONSTRAINT FK_9B5B04E8166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    }
}
