<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402142807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Subject CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE Subject_category CHANGE Subject_id Subject_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE comment CHANGE Subject_id Subject_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE image CHANGE Subject_id Subject_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE user_like CHANGE Subject_id Subject_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Subject CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE Subject_category CHANGE Subject_id Subject_id INT NOT NULL');
        $this->addSql('ALTER TABLE comment CHANGE Subject_id Subject_id INT NOT NULL');
        $this->addSql('ALTER TABLE image CHANGE Subject_id Subject_id INT NOT NULL');
        $this->addSql('ALTER TABLE user_like CHANGE Subject_id Subject_id INT DEFAULT NULL');
    }
}
