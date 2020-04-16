<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200416224103 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE actividad_tarea DROP FOREIGN KEY FK_4D14C6BD6014FACA');
        $this->addSql('ALTER TABLE actividad_tarea DROP FOREIGN KEY FK_4D14C6BD6D5BDFE1');
        $this->addSql('ALTER TABLE actividad_tarea ADD id INT AUTO_INCREMENT NOT NULL, CHANGE actividad_id actividad_id INT DEFAULT NULL, CHANGE tarea_id tarea_id INT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE actividad_tarea ADD CONSTRAINT FK_4D14C6BD6014FACA FOREIGN KEY (actividad_id) REFERENCES actividad (id)');
        $this->addSql('ALTER TABLE actividad_tarea ADD CONSTRAINT FK_4D14C6BD6D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE actividad_tarea MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE actividad_tarea DROP FOREIGN KEY FK_4D14C6BD6014FACA');
        $this->addSql('ALTER TABLE actividad_tarea DROP FOREIGN KEY FK_4D14C6BD6D5BDFE1');
        $this->addSql('ALTER TABLE actividad_tarea DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE actividad_tarea DROP id, CHANGE actividad_id actividad_id INT NOT NULL, CHANGE tarea_id tarea_id INT NOT NULL');
        $this->addSql('ALTER TABLE actividad_tarea ADD CONSTRAINT FK_4D14C6BD6014FACA FOREIGN KEY (actividad_id) REFERENCES actividad (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad_tarea ADD CONSTRAINT FK_4D14C6BD6D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad_tarea ADD PRIMARY KEY (actividad_id, tarea_id)');
    }
}
