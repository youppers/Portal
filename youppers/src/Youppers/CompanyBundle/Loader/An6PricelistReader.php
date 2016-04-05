<?php
/**
 * Created by PhpStorm.
 * User: sergio
 * Date: 05/04/16
 * Time: 16.30
 */

namespace Youppers\CompanyBundle\Loader;


use Ddeboer\DataImport\Reader\CountableReaderInterface;

class An6PricelistReader implements CountableReaderInterface, \SeekableIterator
{

    const AN6_LINE_LENGTH = 400;

    /**
     * AN6 file
     *
     * @var \SplFileObject
     */
    protected $file;

    /**
     * Total number of rows in the AN6 file
     *
     * @var int
     */
    protected $count;

    /**
     * Number of the row that contains the column names
     *
     * @var int
     */
    protected $headerRowNumber = 0;

    /**
     * Column headers as read from the CSV file
     *
     * @var array
     */
    protected $columnHeaders;

    protected $an6record = array(
        'TipoRec' => array(1,1),
        'FornCod' => array(12,2),
        'FornRgs' => array(35,14),
        'FornList' => array(12,49),
        'EAN13' => array(13,61),
        'CodArt' => array(15,74),
        'DesArt1' => array(50,89),
        'DesArt2' => array(50,139),
        'CodiceFAM' => array(20,189),
        'UmBase' => array(3,209),
        'UmAlter' => array(3,209),
        'Quantita' => array(10,215,2),
        'PrezzoListino' => array(15,225,4),
        'CodiceIva' => array(6,240),
        'Scelta' => array(3,246),
        'Spalettizzabile' => array(1,249),
        'Moltiplicatore' => array(7,250,2),
        'CoeffSuperficie' => array(8,257,4),
        'Note' => array(35,265),
        'CatSc' => array(12,300),
        'MinAcq' => array(10,312,2),
        'TipoPrezzo' => array(1,323),
        'Stato' => array(1,323),
        'CodArtCm' => array(15,324),
        'Filler' => array(62,339),
    );

    public function __construct(\SplFileObject $file)
    {
        if ($file->getExtension() != 'an6') {
            throw new \Exception("File must have .an6 extension");
        }

        ini_set('auto_detect_line_endings', true);

        $this->file = $file;
        $this->file->setFlags(
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::DROP_NEW_LINE
        );

        $this->columnHeaders = array_keys($this->an6record);
    }

    public function setHeaderRowNumber($rowNumber, $duplicates = null)
    {
        // FIXME should be called only when using CSV
    }
    /**
     * Rewind the file pointer
     *
     * If a header row has been set, the pointer is set just below the header
     * row. That way, when you iterate over the rows, that header row is
     * skipped.
     */
    public function rewind()
    {
        $this->file->rewind();
        if (null !== $this->headerRowNumber) {
            $this->file->seek($this->headerRowNumber + 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->count) {
            $position = $this->key();

            $this->count = iterator_count($this);

            $this->seek($position);
        }

        return $this->count;
    }

    /**
     * Return the current row as a string
     *
     * @return string
     */
    public function current()
    {
        $line = $this->file->current();
        $res = array();

        foreach ($this->an6record as $fieldName => $fieldLengths) {
            if (count($fieldLengths) == 2) {
                list($length,$start) = $fieldLengths;
                $res[$fieldName] = substr($line,$start-1,$length);
            } elseif (count($fieldLengths) == 3) {
                list($length,$start,$decimals) = $fieldLengths;
                $valueInt = substr($line,$start-1,$length-$decimals);
                $valueDec = substr($line,$start-1+$length-$decimals,$decimals);
                $res[$fieldName] = floatval($valueInt . '.' . $valueDec);
            }
        }
        return $res;
    }

    /**
     * Get column headers
     *
     * @return array
     */
    public function getColumnHeaders()
    {
        return array_keys($this->an6record);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->file->next();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->file->valid() && $this->file->current() !== chr(26);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->file->key();
    }

    /**
     * {@inheritdoc}
     */
    public function seek($pointer)
    {
        $this->file->seek($pointer);
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return array();
    }

}