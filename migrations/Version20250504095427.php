<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504095427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        /*
                $this->addSql('ALTER TABLE commande ADD user_id INT NOT NULL');
                $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
                $this->addSql('CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)');
                $this->addSql('ALTER TABLE user ADD nom VARCHAR(255) NOT NULL, ADD prenom VARCHAR(255) NOT NULL, ADD adresse VARCHAR(255) DEFAULT NULL, ADD telephone VARCHAR(255) DEFAULT NULL');
                $this->addSql('DROP TABLE IF EXISTS etudiant');

                $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DFB88E14F');
                $this->addSql('ALTER TABLE commande DROP COLUMN utilisateur_id');
                $this->addSql('DROP TABLE IF EXISTS utilisateur');
        */
            }

            public function down(Schema $schema): void
            {
                /*
                // this down() migration is auto-generated, please modify it to your needs
                $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
                $this->addSql('DROP INDEX IDX_6EEAA67DA76ED395 ON commande');
                $this->addSql('ALTER TABLE commande DROP user_id');
                $this->addSql('ALTER TABLE user DROP nom, DROP prenom, DROP adresse, DROP telephone');
                $this->addSql('CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
                $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(70) NOT NULL, is_verified TINYINT(1) NOT NULL, nom VARCHAR(70) NOT NULL, prenom VARCHAR(70) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
           */ }

}
