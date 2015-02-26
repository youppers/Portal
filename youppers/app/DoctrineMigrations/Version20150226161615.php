<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150226161615 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*
        $this->addSql('ALTER TABLE pricelist DROP FOREIGN KEY FK_5CCFEA6D44F5D008');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD44F5D008');
        $this->addSql('ALTER TABLE youppers_product__product_collection DROP FOREIGN KEY FK_36AE66F844F5D008');
        $this->addSql('ALTER TABLE brand DROP FOREIGN KEY FK_1C52F958979B1AD6');
        $this->addSql('ALTER TABLE company__product_price DROP FOREIGN KEY FK_EA0C360889045958');
        $this->addSql('ALTER TABLE box_product DROP FOREIGN KEY FK_CA889A9C4584665A');
        $this->addSql('ALTER TABLE company__product_price DROP FOREIGN KEY FK_EA0C36084584665A');
        $this->addSql('ALTER TABLE product_model DROP FOREIGN KEY FK_76C909854584665A');
        */

        $this->addSql('RENAME TABLE brand TO youppers_company__brand');
        $this->addSql('RENAME TABLE product TO youppers_company__product');
        $this->addSql('RENAME TABLE company TO youppers_company__company');
        $this->addSql('RENAME TABLE pricelist TO youppers_company__pricelist');
        $this->addSql('RENAME TABLE company__product_price TO youppers_company__product_price');
        
        //$this->addSql('CREATE TABLE youppers_company__brand (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', company_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', logo_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(60) NOT NULL, code VARCHAR(20) NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, description LONGTEXT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_86D66FC9979B1AD6 (company_id), INDEX IDX_86D66FC9F98F144A (logo_id), UNIQUE INDEX company_brand_name_idx (company_id, name), UNIQUE INDEX company_brand_code_idx (company_id, code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        //$this->addSql('CREATE TABLE youppers_company__product (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', brand_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', qr_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(60) NOT NULL, gtin VARCHAR(20) DEFAULT NULL, code VARCHAR(20) NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, url VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_A27C1303CA784C9B (gtin), INDEX IDX_A27C130344F5D008 (brand_id), INDEX IDX_A27C13035AA64A57 (qr_id), UNIQUE INDEX brand_product_code_idx (brand_id, code), UNIQUE INDEX brand_product_code_name_idx (brand_id, code, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        //$this->addSql('CREATE TABLE youppers_company__company (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', logo_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(60) NOT NULL, code VARCHAR(20) NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, description LONGTEXT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_3E891EE15E237E06 (name), UNIQUE INDEX UNIQ_3E891EE177153098 (code), INDEX IDX_3E891EE1F98F144A (logo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        //$this->addSql('CREATE TABLE youppers_company__pricelist (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', brand_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', code VARCHAR(60) NOT NULL, currency CHAR(3) NOT NULL, enabled TINYINT(1) DEFAULT \'1\' NOT NULL, valid_from DATETIME NOT NULL, valid_to DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_EF988FFB77153098 (code), INDEX IDX_EF988FFB44F5D008 (brand_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        //$this->addSql('CREATE TABLE youppers_company__product_price (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', product_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', pricelist_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', price NUMERIC(10, 4) NOT NULL, uom VARCHAR(10) NOT NULL, updated_at DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_1A31C7934584665A (product_id), INDEX IDX_1A31C79389045958 (pricelist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');

        /*
        $this->addSql('ALTER TABLE youppers_company__brand ADD CONSTRAINT FK_86D66FC9979B1AD6 FOREIGN KEY (company_id) REFERENCES youppers_company__company (id)');
        $this->addSql('ALTER TABLE youppers_company__brand ADD CONSTRAINT FK_86D66FC9F98F144A FOREIGN KEY (logo_id) REFERENCES media__media (id)');
        $this->addSql('ALTER TABLE youppers_company__product ADD CONSTRAINT FK_A27C130344F5D008 FOREIGN KEY (brand_id) REFERENCES youppers_company__brand (id)');
        $this->addSql('ALTER TABLE youppers_company__product ADD CONSTRAINT FK_A27C13035AA64A57 FOREIGN KEY (qr_id) REFERENCES youppers__qr (id)');
        $this->addSql('ALTER TABLE youppers_company__company ADD CONSTRAINT FK_3E891EE1F98F144A FOREIGN KEY (logo_id) REFERENCES media__media (id)');
        $this->addSql('ALTER TABLE youppers_company__pricelist ADD CONSTRAINT FK_EF988FFB44F5D008 FOREIGN KEY (brand_id) REFERENCES youppers_company__brand (id)');
        $this->addSql('ALTER TABLE youppers_company__product_price ADD CONSTRAINT FK_1A31C7934584665A FOREIGN KEY (product_id) REFERENCES youppers_company__product (id)');
        $this->addSql('ALTER TABLE youppers_company__product_price ADD CONSTRAINT FK_1A31C79389045958 FOREIGN KEY (pricelist_id) REFERENCES youppers_company__pricelist (id)');
        //$this->addSql('DROP TABLE ');
        //$this->addSql('DROP TABLE company');
        //$this->addSql('DROP TABLE company__product_price');
        //$this->addSql('DROP TABLE pricelist');
        //$this->addSql('DROP TABLE product');
        $this->addSql('ALTER TABLE youppers_product__product_collection DROP FOREIGN KEY FK_36AE66F844F5D008');
        $this->addSql('ALTER TABLE youppers_product__product_collection ADD CONSTRAINT FK_36AE66F844F5D008 FOREIGN KEY (brand_id) REFERENCES youppers_company__brand (id)');
        $this->addSql('ALTER TABLE box_product DROP FOREIGN KEY FK_CA889A9C4584665A');
        $this->addSql('ALTER TABLE box_product ADD CONSTRAINT FK_CA889A9C4584665A FOREIGN KEY (product_id) REFERENCES youppers_company__product (id)');
        */

        $this->addSql('DROP TABLE product_model');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        /*
        $this->addSql('ALTER TABLE youppers_company__product DROP FOREIGN KEY FK_A27C130344F5D008');
        $this->addSql('ALTER TABLE youppers_company__pricelist DROP FOREIGN KEY FK_EF988FFB44F5D008');
        $this->addSql('ALTER TABLE youppers_product__product_collection DROP FOREIGN KEY FK_36AE66F844F5D008');
        $this->addSql('ALTER TABLE youppers_company__product_price DROP FOREIGN KEY FK_1A31C7934584665A');
        $this->addSql('ALTER TABLE box_product DROP FOREIGN KEY FK_CA889A9C4584665A');
        $this->addSql('ALTER TABLE youppers_company__brand DROP FOREIGN KEY FK_86D66FC9979B1AD6');
        $this->addSql('ALTER TABLE youppers_company__product_price DROP FOREIGN KEY FK_1A31C79389045958');
		*/
        
        $this->addSql('RENAME TABLE youppers_company__brand TO brand');
        $this->addSql('RENAME TABLE youppers_company__product TO product');
        $this->addSql('RENAME TABLE youppers_company__company TO company');
        $this->addSql('RENAME TABLE youppers_company__pricelist TO pricelist');
        $this->addSql('RENAME TABLE youppers_company__product_price TO company__product_price');
        
        /*
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958F98F144A FOREIGN KEY (logo_id) REFERENCES media__media (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FF98F144A FOREIGN KEY (logo_id) REFERENCES media__media (id)');
        $this->addSql('ALTER TABLE company__product_price ADD CONSTRAINT FK_EA0C360889045958 FOREIGN KEY (pricelist_id) REFERENCES pricelist (id)');
        $this->addSql('ALTER TABLE company__product_price ADD CONSTRAINT FK_EA0C36084584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE pricelist ADD CONSTRAINT FK_5CCFEA6D44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD5AA64A57 FOREIGN KEY (qr_id) REFERENCES youppers__qr (id)');
        $this->addSql('ALTER TABLE product_model ADD CONSTRAINT FK_76C909854584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE box_product DROP FOREIGN KEY FK_CA889A9C4584665A');
        $this->addSql('ALTER TABLE box_product ADD CONSTRAINT FK_CA889A9C4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE youppers_product__product_collection DROP FOREIGN KEY FK_36AE66F844F5D008');
        $this->addSql('ALTER TABLE youppers_product__product_collection ADD CONSTRAINT FK_36AE66F844F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        */
    }
}
