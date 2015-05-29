<?php
namespace Youppers\CompanyBundle\Loader\ARTELINEA;

use Youppers\CompanyBundle\Entity\Brand;
use Youppers\CompanyBundle\Loader\AbstractPricelistCollectionLoader;
use Youppers\CompanyBundle\Loader\LoaderMapper;

class PricelistLoader extends AbstractPricelistCollectionLoader
{

    static $collections = array(
        'BTRG' => 'Regolo', // basin top
        'BTDC' => 'Decor', // basin top
        'BTM\.' => 'Top', // basin top su misura
        'KZDC' => 'Decor', // alzatine opalite
        'KYDC' => 'Decor', // alzatine opalite
        'PAMT' => 'Metafora', // Panca
        'WMV' => 'Move', // lighting
        'BTA' => 'Top', // basin top su misura angolare
        'BTM' => 'Monolite 2.0', // basin top
        'KRG' => 'Regolo', // opalite
        'KDC' => 'Decor', // opalite
        'KKM' => 'Kimono', // opalite
        'KMF' => 'Metafora', // opalite
        'TMV' => 'Move', // specchi
        'LRG' => 'Regolo', // lighting
        'TBR' => 'Broadway', // specchi
        'TBX' => 'Box', // specchi
        'TBC' => 'Barcode', // specchi
        'TBA' => 'Dado', // specchi
        'TDE' => 'Deco', // specchi
        'BTU' => 'Uno', // basin top
        'UNO' => 'Uno',
        'WL' => 'Lavorazioni',
        'ML' => 'Lavorazioni',
        'SH' => 'Shock',
        'BB' => 'Basin Box', // lavabi cristallo
        'BT' => 'Vision', // basin top
        'KM' => 'Kimono',
        'MF' => 'Metafora',
        'AT' => 'Atollo',
        'FR' => 'Frammento',
        'OM' => 'Tormento', // opalite o ceramica
        'OP' => 'Opus',
        'TT' => 'Tormento',
        'TL' => 'Led', // specchi
        'TA' => 'Argento', // specchi
        'TF' => 'Flash', // specchi
        'TO' => 'Oblo', // specchi
        'TS' => 'Swing', // specchi
        'TB' => 'I Borgia', // specchi
        'BG' => 'I Borgia',
        'DC' => 'Decor',
        'SM' => 'Summa',
        'M2' => 'Monolite 2.0',
        'VR' => 'Vero',
        'DM' => 'Domino',
        'DQ' => 'Domino 44',
        'VT' => 'Vetrinette',
        'K\.' => 'Cubic Fly Vision', // staffe
        'T' => 'Box', // specchi
        'R' => 'Ricambi', // varie collezioni
        'Q' => 'Quadra', // staffe
        'B' => 'Basin', // lavabi
        'C' => 'Colonne',
        'K' => 'Top', // top su misura
        'F' => 'Top', // lavorazioni particolari top
    );

	public function createMapper()
	{
		$mapping = array(
			'code' => function($row) {
                $code = trim($row['Codice Articolo']);
                $marca = trim($row['Marca / Descrizione']);
                if (!empty($marca)) {
                    $marca = preg_replace('/[^a-z_\-0-9]/i',' ',$marca);
                    foreach(explode(" ",$marca) as $atom) {
                        $atom = trim($atom);
                        if (empty($atom)) {
                            continue;
                        }
                        $code .= '-' . substr($atom,0,3);
                    }
                    //$marca = preg_replace("/\ /","-",$marca);;
                    //$code .= '-' . trim($marca);
                }
                return $code;
            },
			'name' => function($row) {
                $name = trim($row['Codice Articolo']);
                $marca = trim($row['Marca / Descrizione']);
                if (!empty($marca)) {
                    $name .= " " . $marca;
                }
				return $name;
			}, 
			'uom' => function($row) { return 'PCE'; },
            'price' => 'Prezzo Listino',
            'collection' => function($row) {
                $articolo = trim($row['Codice Articolo']);
                foreach (self::$collections as $id => $code) {
                    if (preg_match('/^' .$id .'/',$articolo)) {
                        return $code;
                    }
                }
                return "default";
            },

		);
		$mapper = new LoaderMapper($mapping);
		return $mapper;
	}
	
	private $newCollectionProductType;
	
	protected function getNewCollectionProductType(Brand $brand, $code)
	{
		if (!isset($this->newCollectionProductType)) {
			$this->newCollectionProductType= $this->productTypeManager
				->findOneBy(array('code' => 'BASEMOBILEBAGNO'));
		}
		return $this->newCollectionProductType;
	}
	
}