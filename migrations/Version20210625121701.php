<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210625121701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ads (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, users_id INT NOT NULL, growths_id INT NOT NULL, plants_id INT DEFAULT NULL, plant_ads VARCHAR(64) NOT NULL, city VARCHAR(64) DEFAULT NULL, coordinates TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', quantity INT NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status INT NOT NULL, INDEX IDX_7EC9F62012469DE2 (category_id), INDEX IDX_7EC9F62067B3B43D (users_id), INDEX IDX_7EC9F620F79BA095 (growths_id), INDEX IDX_7EC9F62062091EAB (plants_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ads_user (ads_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9DB8399AFE52BF81 (ads_id), INDEX IDX_9DB8399AA76ED395 (user_id), PRIMARY KEY(ads_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE growth (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, ads_id INT NOT NULL, users_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status INT NOT NULL, INDEX IDX_DB021E96FE52BF81 (ads_id), INDEX IDX_DB021E9667B3B43D (users_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE plants (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(64) NOT NULL, variety VARCHAR(64) DEFAULT NULL, difficulty INT DEFAULT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status INT DEFAULT NULL, INDEX IDX_A5AEDC1612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(150) NOT NULL, password VARCHAR(64) NOT NULL, pseudo VARCHAR(32) NOT NULL, adress VARCHAR(64) DEFAULT NULL, city VARCHAR(64) DEFAULT NULL, coordinates TINYTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', avatar VARCHAR(64) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, status SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ads ADD CONSTRAINT FK_7EC9F62012469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE ads ADD CONSTRAINT FK_7EC9F62067B3B43D FOREIGN KEY (users_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ads ADD CONSTRAINT FK_7EC9F620F79BA095 FOREIGN KEY (growths_id) REFERENCES growth (id)');
        $this->addSql('ALTER TABLE ads ADD CONSTRAINT FK_7EC9F62062091EAB FOREIGN KEY (plants_id) REFERENCES plants (id)');
        $this->addSql('ALTER TABLE ads_user ADD CONSTRAINT FK_9DB8399AFE52BF81 FOREIGN KEY (ads_id) REFERENCES ads (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ads_user ADD CONSTRAINT FK_9DB8399AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96FE52BF81 FOREIGN KEY (ads_id) REFERENCES ads (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E9667B3B43D FOREIGN KEY (users_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE plants ADD CONSTRAINT FK_A5AEDC1612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ads_user DROP FOREIGN KEY FK_9DB8399AFE52BF81');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96FE52BF81');
        $this->addSql('ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F62012469DE2');
        $this->addSql('ALTER TABLE plants DROP FOREIGN KEY FK_A5AEDC1612469DE2');
        $this->addSql('ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F620F79BA095');
        $this->addSql('ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F62062091EAB');
        $this->addSql('ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F62067B3B43D');
        $this->addSql('ALTER TABLE ads_user DROP FOREIGN KEY FK_9DB8399AA76ED395');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E9667B3B43D');
        $this->addSql('DROP TABLE ads');
        $this->addSql('DROP TABLE ads_user');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE growth');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE plants');
        $this->addSql('DROP TABLE user');
    }
}
