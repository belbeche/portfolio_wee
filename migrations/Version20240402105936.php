<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402105936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME DEFAULT NULL, active TINYINT(1) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, is_published TINYINT(1) DEFAULT 0 NOT NULL, published_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_23A0E66A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_category (article_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_53A4EDAA7294869C (article_id), INDEX IDX_53A4EDAA12469DE2 (category_id), PRIMARY KEY(article_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', content LONGTEXT DEFAULT NULL, active TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_9474526C7294869C (article_id), INDEX IDX_9474526CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, service VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE devis (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', type_de_site_web VARCHAR(255) DEFAULT NULL, attentes_design_web VARCHAR(255) DEFAULT NULL, description_projet LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', prix INT DEFAULT NULL, email VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_8B27C52BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, article_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_C53D045F166D1F9C (project_id), INDEX IDX_C53D045F7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', receiver_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', sender_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', devis_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, attachment VARCHAR(255) DEFAULT NULL, INDEX IDX_B6BD307FCD53EDB6 (receiver_id), INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307F41DEFADA (devis_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletters (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8ECF000CE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, category VARCHAR(255) NOT NULL, link VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_category (project_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_3B02921A166D1F9C (project_id), INDEX IDX_3B02921A12469DE2 (category_id), PRIMARY KEY(project_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE suivi_demande (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', devis_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', sender_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', receiver_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', content LONGTEXT NOT NULL, priority VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_97A0ADA341DEFADA (devis_id), INDEX IDX_97A0ADA3F624B39D (sender_id), INDEX IDX_97A0ADA3CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, civility VARCHAR(10) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expire_at DATETIME DEFAULT NULL, avatar VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_like (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', created_at DATETIME DEFAULT NULL, INDEX IDX_D6E20C7A7294869C (article_id), INDEX IDX_D6E20C7AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE devis ADD CONSTRAINT FK_8B27C52BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F41DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
        $this->addSql('ALTER TABLE project_category ADD CONSTRAINT FK_3B02921A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_category ADD CONSTRAINT FK_3B02921A12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA341DEFADA FOREIGN KEY (devis_id) REFERENCES devis (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3F624B39D FOREIGN KEY (sender_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user_like ADD CONSTRAINT FK_D6E20C7A7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE user_like ADD CONSTRAINT FK_D6E20C7AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66A76ED395');
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA7294869C');
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA12469DE2');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE devis DROP FOREIGN KEY FK_8B27C52BA76ED395');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F166D1F9C');
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045F7294869C');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FCD53EDB6');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F41DEFADA');
        $this->addSql('ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A166D1F9C');
        $this->addSql('ALTER TABLE project_category DROP FOREIGN KEY FK_3B02921A12469DE2');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA341DEFADA');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3F624B39D');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3CD53EDB6');
        $this->addSql('ALTER TABLE user_like DROP FOREIGN KEY FK_D6E20C7A7294869C');
        $this->addSql('ALTER TABLE user_like DROP FOREIGN KEY FK_D6E20C7AA76ED395');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_category');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE devis');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE newsletters');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_category');
        $this->addSql('DROP TABLE suivi_demande');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_like');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
