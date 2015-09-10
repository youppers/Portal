<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 6/3/15
 * Time: 7:16 PM
 */

namespace Youppers\CustomerBundle;


class YouppersTCPDF extends \TCPDF {

	public function __construct() {
		parent::__construct();
		$this->SetMargins(10,20,10);
	}

    protected $youppersHeaderText;

    public function setHeaderText($text) {
        $this->youppersHeaderText = $text;
    }

    protected $youppersHeaderImage;

    public function setHeaderImage($imgPath) {
        $this->youppersHeaderImage = $imgPath;
    }

    //Page header
    public function Header() {
        // Logo
        $this->Image($this->youppersHeaderImage, 10, 2, 40, 0, 'PNG', 'http://www.youppers.com', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('DejaVu Sans', 'B', 10);
        // Title
        $this->Cell(0, // the cell extends up to the right margin
                    10, // cell height
                    $this->youppersHeaderText,
                    'B', // Border bottom
                    2,  // the current position should go after the call: Bottom
                    'C', 0, '', 0, false, 'T', 'C');
        $this->SetY(20);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().' di '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
