<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Con il catalogo di marzo 2016 Florim ha spostato tutti i sui prodotti a marchio Casamood nel marchio Casa dolce casa
 */
class Version20160403210233 extends AbstractMigration
{
    private $brand_id_casamood = '3743891e-7d2e-11e4-abcc-0800273000da';
    private $brand_id_casadolcecasa='2dc6e8b6-7d2e-11e4-abcc-0800273000da';
    private $migration_at='2016-04-03 21:02:33';

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("update youppers_company__product set brand_id='". $this->brand_id_casadolcecasa ."', updated_at='" . $this->migration_at . "' where brand_id='" . $this->brand_id_casamood . "'");
        $this->addSql("update youppers_product__product_collection set brand_id='". $this->brand_id_casadolcecasa ."', updated_at='" . $this->migration_at . "' where brand_id='" . $this->brand_id_casamood . "'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("update youppers_company__product set brand_id='". $this->brand_id_casamood ."' where updated_at='" . $this->migration_at . "'");
        $this->addSql("update youppers_product__product_collection set brand_id='". $this->brand_id_casamood ."' where updated_at='" . $this->migration_at . "'");
    }
}
