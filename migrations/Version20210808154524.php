<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210808154524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relation message-translation';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE translation ADD message_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE translation ADD CONSTRAINT FK_B469456F537A1329 FOREIGN KEY (message_id) REFERENCES message (id)');
        $this->addSql('CREATE INDEX IDX_B469456F537A1329 ON translation (message_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE translation DROP FOREIGN KEY FK_B469456F537A1329');
        $this->addSql('DROP INDEX IDX_B469456F537A1329 ON translation');
        $this->addSql('ALTER TABLE translation DROP message_id');
    }
}
