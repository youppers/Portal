<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150311164331 extends AbstractMigration
{
    public function up(Schema $schema)
    {
		$this->addSql("ALTER TABLE youppers_product__attribute_standard CHANGE code symbol VARCHAR(60) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
		$this->addSql("ALTER TABLE youppers_product__attribute_standard CHANGE symbol code VARCHAR(60) DEFAULT NULL");    	
    }
}
