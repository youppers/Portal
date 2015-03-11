<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150311163024 extends AbstractMigration
{
    public function up(Schema $schema)
    {
    	$this->addSql("UPDATE media__media SET provider_name = 'youppers.common.provider.pdf' WHERE context = 'pdf'");    	 
    }

    public function down(Schema $schema)
    {
    	$this->addSql("UPDATE media__media SET provider_name = 'sonata.media.provider.file' WHERE context = 'pdf'");    	 
    }
}
