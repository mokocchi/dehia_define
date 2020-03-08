<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200308195458 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE salto (id INT AUTO_INCREMENT NOT NULL, planificacion_id INT NOT NULL, origen_id INT NOT NULL, respuesta VARCHAR(255) DEFAULT NULL, condicion VARCHAR(255) NOT NULL, INDEX IDX_2C590F1B4428E082 (planificacion_id), INDEX IDX_2C590F1B93529ECD (origen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE salto_tarea (salto_id INT NOT NULL, tarea_id INT NOT NULL, INDEX IDX_DA8B25DFE31D7C12 (salto_id), INDEX IDX_DA8B25DF6D5BDFE1 (tarea_id), PRIMARY KEY(salto_id, tarea_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipo_tarea (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, codigo VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE estado (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dominio (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE actividad (id INT AUTO_INCREMENT NOT NULL, idioma_id INT DEFAULT NULL, dominio_id INT DEFAULT NULL, tipo_planificacion_id INT DEFAULT NULL, planificacion_id INT DEFAULT NULL, autor_id INT DEFAULT NULL, estado_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, objetivo VARCHAR(255) NOT NULL, codigo VARCHAR(255) NOT NULL, INDEX IDX_8DF2BD06DEDC0611 (idioma_id), INDEX IDX_8DF2BD06B105BE34 (dominio_id), INDEX IDX_8DF2BD06E1F40F99 (tipo_planificacion_id), UNIQUE INDEX UNIQ_8DF2BD064428E082 (planificacion_id), INDEX IDX_8DF2BD0614D45BBE (autor_id), INDEX IDX_8DF2BD069F5A440B (estado_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE actividad_tarea (actividad_id INT NOT NULL, tarea_id INT NOT NULL, INDEX IDX_4D14C6BD6014FACA (actividad_id), INDEX IDX_4D14C6BD6D5BDFE1 (tarea_id), PRIMARY KEY(actividad_id, tarea_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE autor (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, apellido VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, googleid VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tipo_planificacion (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarea (id INT AUTO_INCREMENT NOT NULL, dominio_id INT DEFAULT NULL, tipo_id INT DEFAULT NULL, autor_id INT DEFAULT NULL, estado_id INT DEFAULT NULL, nombre VARCHAR(255) NOT NULL, consigna VARCHAR(255) NOT NULL, extra JSON DEFAULT NULL, codigo VARCHAR(255) NOT NULL, INDEX IDX_3CA05366B105BE34 (dominio_id), INDEX IDX_3CA05366A9276E6C (tipo_id), INDEX IDX_3CA0536614D45BBE (autor_id), INDEX IDX_3CA053669F5A440B (estado_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE planificacion (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarea_opcional (planificacion_id INT NOT NULL, tarea_id INT NOT NULL, INDEX IDX_6D2EFEA44428E082 (planificacion_id), INDEX IDX_6D2EFEA46D5BDFE1 (tarea_id), PRIMARY KEY(planificacion_id, tarea_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tarea_inicial (planificacion_id INT NOT NULL, tarea_id INT NOT NULL, INDEX IDX_7ED58D8B4428E082 (planificacion_id), INDEX IDX_7ED58D8B6D5BDFE1 (tarea_id), PRIMARY KEY(planificacion_id, tarea_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE idioma (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(255) NOT NULL, code VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE salto ADD CONSTRAINT FK_2C590F1B4428E082 FOREIGN KEY (planificacion_id) REFERENCES planificacion (id)');
        $this->addSql('ALTER TABLE salto ADD CONSTRAINT FK_2C590F1B93529ECD FOREIGN KEY (origen_id) REFERENCES tarea (id)');
        $this->addSql('ALTER TABLE salto_tarea ADD CONSTRAINT FK_DA8B25DFE31D7C12 FOREIGN KEY (salto_id) REFERENCES salto (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE salto_tarea ADD CONSTRAINT FK_DA8B25DF6D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_8DF2BD06DEDC0611 FOREIGN KEY (idioma_id) REFERENCES idioma (id)');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_8DF2BD06B105BE34 FOREIGN KEY (dominio_id) REFERENCES dominio (id)');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_8DF2BD06E1F40F99 FOREIGN KEY (tipo_planificacion_id) REFERENCES tipo_planificacion (id)');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_8DF2BD064428E082 FOREIGN KEY (planificacion_id) REFERENCES planificacion (id)');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_8DF2BD0614D45BBE FOREIGN KEY (autor_id) REFERENCES autor (id)');
        $this->addSql('ALTER TABLE actividad ADD CONSTRAINT FK_8DF2BD069F5A440B FOREIGN KEY (estado_id) REFERENCES estado (id)');
        $this->addSql('ALTER TABLE actividad_tarea ADD CONSTRAINT FK_4D14C6BD6014FACA FOREIGN KEY (actividad_id) REFERENCES actividad (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actividad_tarea ADD CONSTRAINT FK_4D14C6BD6D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tarea ADD CONSTRAINT FK_3CA05366B105BE34 FOREIGN KEY (dominio_id) REFERENCES dominio (id)');
        $this->addSql('ALTER TABLE tarea ADD CONSTRAINT FK_3CA05366A9276E6C FOREIGN KEY (tipo_id) REFERENCES tipo_tarea (id)');
        $this->addSql('ALTER TABLE tarea ADD CONSTRAINT FK_3CA0536614D45BBE FOREIGN KEY (autor_id) REFERENCES autor (id)');
        $this->addSql('ALTER TABLE tarea ADD CONSTRAINT FK_3CA053669F5A440B FOREIGN KEY (estado_id) REFERENCES estado (id)');
        $this->addSql('ALTER TABLE tarea_opcional ADD CONSTRAINT FK_6D2EFEA44428E082 FOREIGN KEY (planificacion_id) REFERENCES planificacion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tarea_opcional ADD CONSTRAINT FK_6D2EFEA46D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tarea_inicial ADD CONSTRAINT FK_7ED58D8B4428E082 FOREIGN KEY (planificacion_id) REFERENCES planificacion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tarea_inicial ADD CONSTRAINT FK_7ED58D8B6D5BDFE1 FOREIGN KEY (tarea_id) REFERENCES tarea (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE salto_tarea DROP FOREIGN KEY FK_DA8B25DFE31D7C12');
        $this->addSql('ALTER TABLE tarea DROP FOREIGN KEY FK_3CA05366A9276E6C');
        $this->addSql('ALTER TABLE actividad DROP FOREIGN KEY FK_8DF2BD069F5A440B');
        $this->addSql('ALTER TABLE tarea DROP FOREIGN KEY FK_3CA053669F5A440B');
        $this->addSql('ALTER TABLE actividad DROP FOREIGN KEY FK_8DF2BD06B105BE34');
        $this->addSql('ALTER TABLE tarea DROP FOREIGN KEY FK_3CA05366B105BE34');
        $this->addSql('ALTER TABLE actividad_tarea DROP FOREIGN KEY FK_4D14C6BD6014FACA');
        $this->addSql('ALTER TABLE actividad DROP FOREIGN KEY FK_8DF2BD0614D45BBE');
        $this->addSql('ALTER TABLE tarea DROP FOREIGN KEY FK_3CA0536614D45BBE');
        $this->addSql('ALTER TABLE actividad DROP FOREIGN KEY FK_8DF2BD06E1F40F99');
        $this->addSql('ALTER TABLE salto DROP FOREIGN KEY FK_2C590F1B93529ECD');
        $this->addSql('ALTER TABLE salto_tarea DROP FOREIGN KEY FK_DA8B25DF6D5BDFE1');
        $this->addSql('ALTER TABLE actividad_tarea DROP FOREIGN KEY FK_4D14C6BD6D5BDFE1');
        $this->addSql('ALTER TABLE tarea_opcional DROP FOREIGN KEY FK_6D2EFEA46D5BDFE1');
        $this->addSql('ALTER TABLE tarea_inicial DROP FOREIGN KEY FK_7ED58D8B6D5BDFE1');
        $this->addSql('ALTER TABLE salto DROP FOREIGN KEY FK_2C590F1B4428E082');
        $this->addSql('ALTER TABLE actividad DROP FOREIGN KEY FK_8DF2BD064428E082');
        $this->addSql('ALTER TABLE tarea_opcional DROP FOREIGN KEY FK_6D2EFEA44428E082');
        $this->addSql('ALTER TABLE tarea_inicial DROP FOREIGN KEY FK_7ED58D8B4428E082');
        $this->addSql('ALTER TABLE actividad DROP FOREIGN KEY FK_8DF2BD06DEDC0611');
        $this->addSql('DROP TABLE salto');
        $this->addSql('DROP TABLE salto_tarea');
        $this->addSql('DROP TABLE tipo_tarea');
        $this->addSql('DROP TABLE estado');
        $this->addSql('DROP TABLE dominio');
        $this->addSql('DROP TABLE actividad');
        $this->addSql('DROP TABLE actividad_tarea');
        $this->addSql('DROP TABLE autor');
        $this->addSql('DROP TABLE tipo_planificacion');
        $this->addSql('DROP TABLE tarea');
        $this->addSql('DROP TABLE planificacion');
        $this->addSql('DROP TABLE tarea_opcional');
        $this->addSql('DROP TABLE tarea_inicial');
        $this->addSql('DROP TABLE idioma');
    }
}
