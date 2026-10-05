<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260930065525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT NOT NULL, jv_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_8F91ABF0C2F47B93 (jv_id), INDEX IDX_8F91ABF0FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE jv (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, date_sortie DATE NOT NULL, description LONGTEXT DEFAULT NULL, detail LONGTEXT DEFAULT NULL, image VARCHAR(255) NOT NULL, genre_id INT NOT NULL, INDEX IDX_67AD45DB4296D31F (genre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, valeur INT NOT NULL, jv_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_CFBDFA14C2F47B93 (jv_id), INDEX IDX_CFBDFA14FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE plateforme (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, mail VARCHAR(180) NOT NULL, mdp VARCHAR(255) NOT NULL, pseudo VARCHAR(100) NOT NULL, photo_profil VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B35126AC48 (mail), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0C2F47B93 FOREIGN KEY (jv_id) REFERENCES jv (id)');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE jv ADD CONSTRAINT FK_67AD45DB4296D31F FOREIGN KEY (genre_id) REFERENCES genre (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14C2F47B93 FOREIGN KEY (jv_id) REFERENCES jv (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0C2F47B93');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0FB88E14F');
        $this->addSql('ALTER TABLE jv DROP FOREIGN KEY FK_67AD45DB4296D31F');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14C2F47B93');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14FB88E14F');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE jv');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE plateforme');
        $this->addSql('DROP TABLE utilisateur');
    }
}
