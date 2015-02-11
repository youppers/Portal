<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150211013723 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
    	$this->addSql("update youppers__qr set target_type='youppers_dealer_box' where target_type='youpper_dealer_box'");
    	$this->addSql("update youppers__qr set target_type='youppers_company_product' where target_type='youpper_company_product'");
        $this->addSql("update youppers__qr set target_type='youppers_company_variation' where target_type='youpper_company_variation'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
