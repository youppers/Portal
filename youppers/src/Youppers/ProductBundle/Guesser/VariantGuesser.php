<?php
namespace Youppers\ProductBundle\Guesser;

use Youppers\ProductBundle\Guesser\BaseVariantGuesser;

class VariantGuesser extends BaseVariantGuesser
{
    public function guess() {
        $this->addTodo("<error>Using default guesser</error>");
        parent::guess();
    }

}