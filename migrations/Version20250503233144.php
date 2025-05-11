<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503233144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE ligne_commande ADD livre_id INT NOT NULL');
        //$this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B37D925CB FOREIGN KEY (livre_id) REFERENCES livres (id)');
        //$this->addSql('CREATE INDEX IDX_3170B74B37D925CB ON ligne_commande (livre_id)');
        //$this->addSql('ALTER TABLE livres ADD stock INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B37D925CB');
        //$this->addSql('DROP INDEX IDX_3170B74B37D925CB ON ligne_commande');
        //$this->addSql('ALTER TABLE ligne_commande DROP livre_id');
       // $this->addSql('ALTER TABLE livres DROP stock');
    }
}
