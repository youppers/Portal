<?php
namespace Youppers\ProductBundle\Tests\Controller;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Guesser\TileDimPropertyGuesser;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;

class TileDimPropertyGuesserTest extends KernelTestCase
{

	/** @var  TileDimPropertyGuesser */
	private $tileDimPropertyGuesser;

	public function setUp()
	{
		static::bootKernel();

		/** @var AttributeType $t */
		$t = new AttributeType();

		/** @var ManagerRegistry $managerRegistry */
		$managerRegistry = static::$kernel->getContainer()->get('doctrine');

		/** @var VariantPropertyManager $vpm */
		$vpm = new VariantPropertyManager($managerRegistry);

		/** @var AttributeOptionManager $aom */
		$aom = new AttributeOptionManager($managerRegistry);

		$this->tileDimPropertyGuesser = new TileDimPropertyGuesser($t,$vpm,$aom);
		$this->tileDimPropertyGuesser->setLogger(static::$kernel->getContainer()->get('logger'));
	}

	public function testNormalizeValueOk()
	{
		$this->assertSame(null,$this->tileDimPropertyGuesser->normalizeValue(null));
		$this->assertSame('',$this->tileDimPropertyGuesser->normalizeValue(''));

		$this->assertSame('1X2',$this->tileDimPropertyGuesser->normalizeValue('1X2'));
		$this->assertSame('0X0',$this->tileDimPropertyGuesser->normalizeValue('0x0'));
		//$this->assertSame('0X0',$this->tileDimPropertyGuesser->normalizeValue('0X'));
		//$this->assertSame('0X0',$this->tileDimPropertyGuesser->normalizeValue('X0'));
		//$this->assertSame('0X1',$this->tileDimPropertyGuesser->normalizeValue('X1'));
		//$this->assertSame('1X0',$this->tileDimPropertyGuesser->normalizeValue('1X'));

		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('12,3X34,5'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('12.3X34,5'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('12.3X34.5'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('012,3X34.5'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('012,3X34.5'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('12,3 X 34.5'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('012,30 X 034,50'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('012.30 x 034,50'));
		$this->assertSame('12,3X34,5',$this->tileDimPropertyGuesser->normalizeValue('012,30 x 034.50'));
		$this->assertSame('12,3X34',$this->tileDimPropertyGuesser->normalizeValue('012,30 x 034.00'));
		$this->assertSame('12X34,5',$this->tileDimPropertyGuesser->normalizeValue('012,00 x 034.50'));
		$this->assertSame('12X34',$this->tileDimPropertyGuesser->normalizeValue('012,00 x 034.00'));

		$this->assertSame('120X340',$this->tileDimPropertyGuesser->normalizeValue('120x340'));
		$this->assertSame('0,1X2',$this->tileDimPropertyGuesser->normalizeValue('0,1X2'));
		$this->assertSame('0,1X2',$this->tileDimPropertyGuesser->normalizeValue('0,1X2,0'));

		$this->assertSame('2X50,2',$this->tileDimPropertyGuesser->normalizeValue('2,0X50,2'));
	}

	public function testNormalizeValueFail()
	{
		$this->assertNotSame('2X50,2',$this->tileDimPropertyGuesser->normalizeValue('1, 2 X 3,4'));
		$this->assertNotSame('2X50,2',$this->tileDimPropertyGuesser->normalizeValue('1..2 X 3,4'));
	}

}