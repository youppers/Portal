<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150226173732 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE box_product DROP FOREIGN KEY FK_CA889A9CD8177B3F');
        $this->addSql('ALTER TABLE store DROP FOREIGN KEY FK_FF575877249E6EA1');
        $this->addSql('ALTER TABLE box DROP FOREIGN KEY FK_8A9483AB092A811');

        $this->addSql('RENAME TABLE box TO youppers_dealer__box');
        $this->addSql('RENAME TABLE box_product TO youppers_dealer__box_product');
        $this->addSql('RENAME TABLE dealer TO youppers_dealer__dealer');
        $this->addSql('RENAME TABLE store TO youppers_dealer__store');
                
        $this->addSql('ALTER TABLE youppers_dealer__box_product ADD CONSTRAINT FK_E91A9C39D8177B3F FOREIGN KEY (box_id) REFERENCES youppers_dealer__box (id)');
        $this->addSql('ALTER TABLE youppers_dealer__box_product ADD CONSTRAINT FK_E91A9C394584665A FOREIGN KEY (product_id) REFERENCES youppers_company__product (id)');
        $this->addSql('ALTER TABLE youppers_dealer__store ADD CONSTRAINT FK_E30E6BBB249E6EA1 FOREIGN KEY (dealer_id) REFERENCES youppers_dealer__dealer (id)');
        $this->addSql('ALTER TABLE youppers_dealer__box ADD CONSTRAINT FK_EFAFE312B092A811 FOREIGN KEY (store_id) REFERENCES youppers_dealer__store (id)');
        $this->addSql('ALTER TABLE youppers_dealer__box ADD CONSTRAINT FK_EFAFE3125AA64A57 FOREIGN KEY (qr_id) REFERENCES youppers__qr (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->abortIf(true);
    }
}
