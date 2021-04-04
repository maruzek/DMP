<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210401172342 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE follow ADD followed DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE index_block ADD added DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD posted_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD created DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE project_admin ADD date_added DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE seen CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user ADD first_login DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE follow DROP followed');
        $this->addSql('ALTER TABLE index_block DROP added');
        $this->addSql('ALTER TABLE post DROP posted_date');
        $this->addSql('ALTER TABLE project DROP created');
        $this->addSql('ALTER TABLE project_admin DROP date_added');
        $this->addSql('ALTER TABLE seen CHANGE date date DATE NOT NULL');
        $this->addSql('ALTER TABLE user DROP first_login');
    }
}
