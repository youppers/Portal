<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Entity\AttributeStandard;
use Youppers\ProductBundle\Entity\ProductVariant;
use Youppers\ProductBundle\Entity\ProductCollection;
use Youppers\ProductBundle\Entity\AttributeType;
use Youppers\ProductBundle\Manager\AttributeOptionManager;
use Youppers\ProductBundle\Manager\VariantPropertyManager;
use Youppers\ProductBundle\Entity\AttributeOption;

class BasePropertyGuesser extends AbstractGuesser
{

	private $type;
	
    private $isVariant = false;

    protected $defaultOption;

    public function setIsVariant($isVariant)
    {
       $this->isVariant = $isVariant;
    }

	/**
	 * @var boolean $autoAddOptions
	 * True only if the concrete guesser is confident to automatuically add options to a standard
	 */
	protected $autoAddOptions = false;

	protected $variantPropertyManager;

	protected $attributeOptionManager;

	public function __construct(AttributeType $type, VariantPropertyManager $variantPropertyManager, AttributeOptionManager $attributeOptionManager)
	{
		$this->type = $type;
		$this->variantPropertyManager = $variantPropertyManager;
		$this->attributeOptionManager = $attributeOptionManager;
	}

    public function getType() {
        return $this->type;
    }

	/**
	 * @return string|null The name of the default standard name for this property
	 */
	public function getDefaultStandardName()
	{
		return null;
	}


	/**
	 * @return string Column where to find the value of the type
	 * The default is the code of the type, but subclasses can override
	 */
	public function getTypeColumn() {
		return $this->getType()->getCode();
	}

	/**
	 * @param ProductVariant $variant
	 * @param string $text Search values of the attributes here
	 * @param bool $textIsValue text is a value, so must be searched as is
	 */
	public function guessVariant(ProductVariant $variant, &$text, $textIsValue = false) {
		return $this->guessProperty($variant, $text, $this->type, $textIsValue);
	}
	
	/**
	 * array( value => array( type => array( standard => option) ) )
	 */	
	private $options = null;
	
	protected function getActualOption(ProductVariant $variant,AttributeType $type)
	{
		$actualoption = null;
		foreach ($variant->getVariantProperties() as $property) {
			if ($property->getAttributeOption()->getAttributeStandard()->getAttributeType() === $type) {
				$actualoption = $property->getAttributeOption();
				break;
			}
		}
		return $actualoption;
	}
	
	private $collectionOptions = array();

	private $collectionStandards = array();

	protected function getCollectionStandards(ProductCollection $collection, AttributeType $type) {
		if (array_key_exists($collection->getId(),$this->collectionStandards) && array_key_exists($type->getId(),$this->collectionStandards[$collection->getId()])) {
			return $this->collectionStandards[$collection->getId()][$type->getId()];
		}
		$standards = array();
		foreach ($collection->getStandards()->getValues() as $standard) {
			if ($standard->getAttributeType() == $type) {
				$standards[] = $standard;
			}
		}
		$this->collectionStandards[$collection->getId()][$type->getId()] = $standards;
		return $standards;
	}

	protected function getDefaultStandard(ProductVariant $variant, AttributeType $type)
	{
		$standards = $this->getCollectionStandards($variant->getProductCollection(), $type);
		foreach ($standards as $standard) {
			if ($standard->getName() == $this->getDefaultStandardName()) {
				return $standard;
			}
		}
		return null;
	}

	protected function getCollectionOptions(ProductCollection $collection, AttributeType $type)
	{
		if (!array_key_exists($collection->getId(),$this->collectionOptions)) {
			$this->collectionOptions[$collection->getId()] = array();
		}
		if (!array_key_exists($type->getId(),$this->collectionOptions[$collection->getId()])) {
			$options = array();
			$standards = array();
			foreach ($collection->getStandards()->getValues() as $standard) {
				if ($standard->getAttributeType() == $type) {
					$standards[] = $standard;
					foreach ($this->attributeOptionManager->findBy(array('attributeStandard' => $standard)) as $option) {
                        if (!$option->getEnabled()) {
                            continue;
                        }
                        if (!$standard->getUsesOnlyAlias()) {
                            $options[trim($option->getValue())] = $option;
                        }
						foreach (explode(';',$option->getAlias()) as $alias) {
                            $alias = trim($alias);
							if (!empty($alias)) {
                                if (array_key_exists($alias,$options)) {
                                    $msg = sprintf("Alias '%s' of '%s' conflict with '%s'",$alias,$option,$options[$alias]);
                                    $this->getLogger()->warning($msg);
                                    $todo = sprintf("<error>Correct error in standard options</error> <info>%s</info> then redo guessing.",$msg);
                                    $this->addTodo($todo);
                                    break;
                                }
								$options[$alias] = $option;
							}
						}
					}
				}
				$this->getLogger()->debug(sprintf("Collection '%s': loaded %d options (with alias) for standard '%s'",$collection,count($options),$standard));
				if ($this->getDebug()) {
					$this->getLogger()->debug($standard . ':' . implode(' ',array_keys($options)));
				}
			}
            uksort($options,function($a, $b) { return strlen($b) - strlen($a);});
			$this->collectionOptions[$collection->getId()][$type->getId()] = $options;
			$this->collectionStandards[$collection->getId()][$type->getId()] = $standards;
			if (count($standards) == 0) {
				$todo = sprintf("<error>Add standard</error> of type <info>%s</info> for collection <info>%s</info> then redo guessing.", $type, $collection);
				$this->addTodo($todo);
			} else if (count($options) == 0) {
				if (!$this->autoAddOptions) {
					foreach ($standards as $standard) {
						$todo = sprintf("<error>Add options</error> to standard <info>%s</info> then redo guessing.",$standard);
						$this->addTodo($todo);
					}
				}
			} else {
				$this->getLogger()->debug(sprintf("Cached %d options of type '%s' for collection '%s'",count($options),$type,$collection));
			}
		}
		return $this->collectionOptions[$collection->getId()][$type->getId()];
	}

	/**
	 * @param $value string
	 * @param AttributeStandard $standard
	 * @return string The value normalized for the standard
	 * @throws \InvalidArgumentException if the value doesn't conform to the standard
	 */
	protected function normalizeValue($value,AttributeStandard $standard=null)
	{
		return $value;
	}

	private function createOption(ProductVariant $variant, AttributeType $type, $value)
	{
		$standards = $this->collectionStandards[$variant->getProductCollection()->getId()][$type->getId()];
		if (count($standards) == 0) {
			$this->getLogger()->error(sprintf("Cannot autoadd if the collection '%s' dont have standard of type '%s'", $variant->getProductCollection(), $type));
			return null;
		}
		$standard = $this->getDefaultStandard($variant, $type);
		if (empty($standard)) {
			$this->getLogger()->error(sprintf("Cannot autoadd if the collection '%s' don't have standard '%s'", $variant->getProductCollection(), $this->getDefaultStandardName()));
			return null;
		}
		$value = $this->normalizeValue($value,$standard);
		if (empty($value)) {
			$this->getLogger()->error(sprintf("Cannot autoadd not normalized value to '%s'",$standard));
			return null;
		}
		foreach ($this->getCollectionOptions($variant->getProductCollection(), $type) as $option) {
			if ($option->getValue() == $value) {
				$this->getLogger()->error(sprintf("Already exists option '%s' with value '%s' in '%s'", $option, $value,$standard));
				return null;
			}
		}
		$option = $this->attributeOptionManager->create();
		$option->setAttributeStandard($standard);
		$option->setValue(trim($value));
		$option->setEnabled(true);
		$option->setPosition(count($this->collectionOptions[$variant->getProductCollection()->getId()][$type->getId()]) + 1);
		$this->collectionOptions[$variant->getProductCollection()->getId()][$type->getId()][$value] = $option;
		$this->getLogger()->info(sprintf("Add new property '%s' for '%s'", $option, $variant));
		if ($this->getForce()) {
			$this->attributeOptionManager->save($option,false);
		}
		return $option;
	}

    /**
     * If the value of the option has multiple words, all words must be in the text
     * @param ProductVariant $variant
     * @param string $text Search options in this string
     * @param $type AttributeType of attribute to search
	 * @param $textIsValue text is a value, so must be searched as is
     */
    protected function guessProperty(ProductVariant $variant, &$text, AttributeType $type, $textIsValue = false)
	{
		$actualOption = $this->getActualOption($variant, $type);
		$newText = trim($text);
		if ($textIsValue) {
			$newText = $this->normalizeValue($newText);
		}
		$options = $this->getCollectionOptions($variant->getProductCollection(), $type);
		foreach ($options as $values => $option) {
            $match = false;
			$valuesWorlds = explode(" ",$values);
            usort($valuesWorlds,function($a, $b) { return strlen($b) - strlen($a);});
            foreach ($valuesWorlds as $value) {
                $regexp = trim($value);
                if (empty($regexp)) {
                    continue;
                }
				$regexp = preg_quote($regexp,'/');
				if (preg_match("/[0-9]$/",$regexp)) {
					$regexp = $regexp . '[^0-9,]';
				} elseif (preg_match("/\.$/",$regexp)) {
					// match end as is
				} else {
					$regexp = $regexp . '[0-9\s\.]';
				}
				if (preg_match("/^[0-9]/",$regexp)) {
					$regexp = '[^0-9,]' . $regexp;
				} else {
					$regexp = '[0-9\s\.]' . $regexp;
				}
                $match = preg_match('/' . $regexp . '/i', " ".iconv("UTF-8", "ASCII//TRANSLIT", $newText)." ");
                if (!$match) {
                    break;
                }
                if ($option->getAttributeStandard()->getRemoveMatchingWords()) {
                    $newText = trim(preg_replace('/' . preg_quote($value,'/') . '/i','',$newText));
                }
            }
            if ($match) $requiredOptions = $option->getAttributeStandard()->getRequiredOptions();
            if ($match && $requiredOptions->count()) {
                $match = false;
                foreach ($requiredOptions as $requiredOption) {
                    $variantProperties = $variant->getVariantProperties();
                    foreach ($variantProperties as $variantProperty) {
                        if ($requiredOption == $variantProperty->getAttributeOption()) {
                            $match = true;
                            continue 2;
                        }
                    }
                }
                if (!$match) {
                    $this->getLogger()->debug(sprintf("Variant '%s' guessed property '%s' but miss required options",$variant,$option));
                }
            }
			if ($match) {
                //dump(array('text' => $text, 'newText' => $newText));
                $text = $newText;
				if ($actualOption) {
					if ($option === $actualOption) {
						$this->getLogger()->debug(sprintf("Variant '%s' guessed property '%s' match actual",$variant,$option));
					} else {
                        if ($this->getForce()) {
                            $this->changeVariantProperty($variant,$actualOption,$option);
							$this->getLogger()->warning(sprintf("Changed property from '%s' to '%s' for '%s'",$option,$actualOption,$variant));
                        } else {
                            $todo = sprintf("<question>Change property</question> <info>%s</info> from <info>%s</info> to <question>%s</question> <info>%s</info> for <info>%s</info>",
                                $option->getAttributeType(), $actualOption->getAttributeStandard() . ': ' . $actualOption->getValueWithSymbol(), empty($option->getId()) ? 'new':'', $option->getAttributeStandard() . ': ' . $option->getValueWithSymbol(), $variant->getProduct()->getNameCode());
                            $this->addTodo($todo);
                        }
					}
				} else {
					if (empty($option->getId())) {
						if ($this->getForce()) {
							$this->addVariantProperty($variant,$option);
							$this->getLogger()->info(sprintf("Added new property '%s' to '%s'",$option,$variant));
						} else {
							$todo = sprintf("<question>Add new property</question> <info>%s</info> to <info>%s</info>",$option,$variant->getProduct()->getNameCode());
							$this->addTodo($todo);
						}
					} elseif ($this->getWrite()) {
						$this->addVariantProperty($variant,$option);
					} else {
						$todo = sprintf("<question>Add property</question> <info>%s</info> to <info>%s</info>",$option,$variant->getProduct()->getNameCode());
						$this->addTodo($todo);
					}
				}
                if ($option->getAttributeStandard()->getIsVariantImage() && $variant->getImage() == null) {
                    if ($this->getWrite()) {
                        $this->setVariantImageOption($variant,$option);
						$this->getLogger()->info(sprintf("Added option image '%s' to '%s'",$option->getImage(),$variant));
                    } else {
                        $todo = sprintf("<question>Add image</question> <info>%s</info> to <info>%s</info>",$option->getImage(),$variant->getProduct()->getNameCode());
                        $this->addTodo($todo);
                    }
                }
				return true;
			}
		}
        if (empty($actualOption)
			&& !($this->autoAddOptions && $textIsValue)
			 && $this->hasDefaultOption($variant, $type)) {
            $option = $this->getDefaultOption($variant, $type);
            if ($option === false) {
                $this->getLogger()->debug(sprintf("Default option false for '%s' type '%s'", $variant, $type));
            } else {
                if ($this->getForce()) {
                    $this->addVariantProperty($variant, $option);
					$this->getLogger()->debug(sprintf("Added default option '%s' to '%s'", $option, $variant));
                } else {
                    $todo = sprintf("<question>Add default property</question> <info>%s</info> to <info>%s</info>", $option, $variant->getProduct()->getNameCode());
                    $this->addTodo($todo);
                }
				return true;
            }
		}
		if ($this->autoAddOptions && $textIsValue) {
			$option = $this->createOption($variant, $type, $text);
			if ($option === null) {
				$todo = sprintf("<error>Cannot add option</error> <info>%s</info> of type <info>%s</info>", $text, $type);
				$this->addTodo($todo);
			} else {
				if (empty($actualOption)) {
					if ($this->getForce()) {
						$this->addVariantProperty($variant, $option);
					} else {
						$todo = sprintf("<question>Add new property</question> <info>%s</info> to <info>%s</info>", $option, $variant->getProduct()->getNameCode());
						$this->addTodo($todo);
					}
				} else {
					$this->getLogger()->warning(sprintf("Variant '%s' new guessed property '%s' don't match actual '%s'", $variant, $option, $actualOption));
					if ($this->getForce()) {
						$this->changeVariantProperty($variant, $actualOption, $option);
					} else {
						$todo = sprintf("<question>Change property</question> <info>%s</info> from <info>%s</info> to <question>new</question> <info>%s</info> for <info>%s</info>",
							$option->getAttributeType(), $actualOption->getAttributeStandard() . ': ' . $actualOption->getValueWithSymbol(), $option->getAttributeStandard() . ': ' . $option->getValueWithSymbol(), $variant->getProduct()->getNameCode());
						$this->addTodo($todo);
					}
				}
				return true;
			}
		}
		if (empty($actualOption)) {
			if (count($options)) {
				if ($this->isVariant) {
					$todo = sprintf("<error>Not guessed</error> property of type <info>%s</info> for <info>%s</info>",$type,$variant->getProduct()->getNameCode());
					if ($textIsValue) {
						$todo .= " value=" . $text;
					}
				} else {
					$todo = sprintf("Not guessed property of type <info>%s</info> for <info>%s</info>",$type,$variant->getProduct()->getNameCode());
					if ($textIsValue) {
						$todo .= " :" . $text;
					}
				}
				$this->addTodo($todo);
			} else {
                // no write when no options: already did in getCollectionOptions
            }
		} else {
			$this->getLogger()->debug(sprintf("Variant '%s' actual %s is '%s'",$variant,$type,$actualOption));
		}
		return false;
	}

	protected function addVariantProperty(ProductVariant $variant,AttributeOption $option)
	{
		$variantProperty = $this->variantPropertyManager->create();
		$variantProperty->setProductVariant($variant);
		$variantProperty->setAttributeOption($option);
		$variantProperty->setPosition(1 + count($variant->getVariantProperties()));
		$variant->addVariantProperty($variantProperty);
		$this->variantPropertyManager->save($variantProperty,false);		
	}

    protected function changeVariantProperty(ProductVariant $variant, AttributeOption $actualOption, AttributeOption $option)
    {
        foreach ($variant->getVariantProperties() as $property) {
            if ($property->getAttributeOption() === $actualOption) {
                $property->setAttributeOption($option);
                break;
            }
        }
    }

    protected function setVariantImageOption(ProductVariant $variant,AttributeOption $option)
    {
        $image = $option->getImage();
        if ($image) {
            $variant->setImage($image);
        }
    }

	
	protected function hasDefaultOption(ProductVariant $variant, AttributeType $type)
	{
		return null !== $this->getDefaultOption($variant,$type);
	}
	
	/**
	 * Override this to provide a default
	 * 
	 * @return AttributeOption
	 */
	protected function getDefaultOption(ProductVariant $variant, AttributeType $type)
	{
        if (null === $this->defaultOption) {
            $options = $variant->getProductCollection()->getDefaults();
            if (empty($options)) {
                $this->defaultOption = false;
            } else {
                foreach ($options as $option) {
                    if ($option->getAttributeType() == $type && $option->getEnabled()) {
                        $this->defaultOption = $option;
                        break;
                    }
                }
            }
        }
        return $this->defaultOption;
	}

}
