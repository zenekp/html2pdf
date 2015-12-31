<?php
/**
 * Html2Pdf Library
 *
 * HTML => PDF converter
 * distributed under the LGPL License
 *
 * @package   Html2pdf
 * @author    Laurent MINGUET <webmaster@html2pdf.fr>
 * @copyright 2016 Laurent MINGUET
 */

namespace Spipu\Html2Pdf;

/**
 * Class Pager
 */
class Pager
{
    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var boolean
     */
    private $firstPage = true;

    /**
     * @var string
     */
    private $orientation = 'P';

    /**
     * @var string
     */
    private $format = 'A4';

    /**
     * default left marge of the page
     * @var float
     */
    private $defaultLeft = 0.;

    /**
     * default top marge of the page
     * @var float
     */
    private $defaultTop = 0.;

    /**
     * default right marge of the page
     * @var float
     */
    private $defaultRight = 0.;

    /**
     * default bottom marge of the page
     * @var float
     */
    private $defaultBottom = 0.;

    /**
     * current left marge of the page
     * @var float
     */
    private $margeLeft = 0.;

    /**
     * current top marge of the page
     * @var float
     */
    private $margeTop = 0.;

    /**
     * current right marge of the page
     * @var float
     */
    private $margeRight = 0.;

    /**
     * current bottom marge of the page
     * @var float
     */
    private $margeBottom = 0.;

    /**
     * save the different states of the current page
     * @var array
     */
    private $states = array();

    /**
     * float marge of the current page
     * @var array
     */
    private $pageMarges = array();

    /**
     * background information
     * @var array
     */
    private $background = array();

    /**
     * the paragraph margins
     * @var null|array
     */
    private $paragraph = null;

    /**
     * @var CssConverter
     */
    private $cssConverter;

    /**
     * @var MyPdf
     */
    private $pdf;

    /**
     * Pager constructor.
     *
     * @param CssConverter $cssConverter
     * @param MyPdf        $pdf
     *
     * @return Pager
     */
    public function __construct(CssConverter $cssConverter, MyPdf $pdf)
    {
        $this->cssConverter = $cssConverter;
        $this->pdf = $pdf;
    }

    /**
     * init the pager
     *
     * @param string $orientation
     * @param string $format
     *
     * @return void
     */
    public function init($orientation, $format)
    {
        $this->firstPage = true;
        $this->page = 0;

        $this->orientation = $orientation;
        $this->format = $format;
        $this->states = array();
    }

    /**
     * get the orientation
     *
     * @return string
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * get the format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * is it the first page ?
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->firstPage;
    }

    /**
     * set the current page number
     *
     * @param int $page
     *
     * @return void
     */
    public function setCurrentPage($page)
    {
        $this->page = $page;
    }

    /**
     * get the current page number
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->page;
    }

    /**
     * get the marge left
     *
     * @return float
     */
    public function getMargeLeft()
    {
        return $this->margeLeft;
    }

    /**
     * get the marge top
     *
     * @return float
     */
    public function getMargeTop()
    {
        return $this->margeTop;
    }

    /**
     * get the marge right
     *
     * @return float
     */
    public function getMargeRight()
    {
        return $this->margeRight;
    }

    /**
     * get the marge bottom
     *
     * @return float
     */
    public function getMargeBottom()
    {
        return $this->margeBottom;
    }
    /**
     * set the paragraph margins
     *
     * @param float|null $left
     * @param float|null $right
     *
     * @return void
     */
    public function setParagraphMargins($left = null, $right = null)
    {
        if (is_null($left) || is_null($right)) {
            $this->paragraph = null;
        } else {
            $this->paragraph = array($left, $right);
        }

    }

    /**
     * set the default margins of the page
     *
     * @param array|int $margins (mm, left top right bottom)
     *
     *  @return void
     */
    public function setDefaultMargins($margins)
    {
        if (!is_array($margins)) {
            $margins = array($margins, $margins, $margins, $margins);
        }

        if (!isset($margins[2])) {
            $margins[2] = $margins[0];
        }
        if (!isset($margins[3])) {
            $margins[3] = 8;
        }

        $this->defaultLeft   = $this->cssConverter->ConvertToMM($margins[0].'mm');
        $this->defaultTop    = $this->cssConverter->ConvertToMM($margins[1].'mm');
        $this->defaultRight  = $this->cssConverter->ConvertToMM($margins[2].'mm');
        $this->defaultBottom = $this->cssConverter->ConvertToMM($margins[3].'mm');
    }

    /**
     * get the default margins
     *
     * @return array
     */
    public function getDefaultMargins()
    {
        return array(
            $this->defaultLeft,
            $this->defaultTop,
            $this->defaultRight,
            $this->defaultBottom
        );
    }

    /**
     * get the key for the float marge array
     *
     * @param float $y
     *
     * @return int
     */
    private function getFloatMargeKey($y)
    {
        return (int) floor($y*100);
    }

    /**
     * set the background info
     *
     * @param array|null $background
     *
     * @return void
     */
    public function setBackground($background)
    {
        $this->background = $background;
    }

    /**
     * set the real margin, using the default margins and the page margins
     *
     * @return void
     */
    public function setMargins()
    {
        // read background marge
        $backgroundLeft   = (isset($this->background['left'])   ? $this->background['left']   : 0);
        $backgroundRight  = (isset($this->background['right'])  ? $this->background['right']  : 0);
        $backgroundTop    = (isset($this->background['top'])    ? $this->background['top']    : 0);
        $backgroundBottom = (isset($this->background['bottom']) ? $this->background['bottom'] : 0);

        // prepare the margins
        $this->margeLeft   = $this->defaultLeft   + $backgroundLeft;
        $this->margeRight  = $this->defaultRight  + $backgroundRight;
        $this->margeTop    = $this->defaultTop    + $backgroundTop;
        $this->margeBottom = $this->defaultBottom + $backgroundBottom;

        // set the PDF margins
        $this->pdf->SetMargins($this->margeLeft, $this->margeTop, $this->margeRight);
        $this->pdf->SetAutoPageBreak(false, $this->margeBottom);

        // set the float Margins
        $this->pageMarges = array();

        if (is_array($this->paragraph)) {
            $this->addMargin($this->margeTop, $this->paragraph[0], $this->pdf->getW()-$this->paragraph[1]);
        } else {
            $this->addMargin($this->margeTop, $this->margeLeft, $this->pdf->getW()-$this->margeRight);
        }
    }

    /**
     * add a margin for floats
     *
     * @param float $y
     * @param float $left
     * @param float $right
     *
     * @return void
     */
    public function addMargin($y, $left, $right)
    {
        $y = $this->getFloatMargeKey($y);

        $this->pageMarges[$y] = array($left, $right);

        ksort($this->pageMarges);
    }


    /**
     * Add box margins, for a float
     *
     * @param string $float (left / right)
     * @param float  $xLeft
     * @param float  $yTop
     * @param float  $xRight
     * @param float  $yBottom
     *
     * @return void
     */
    public function addBoxMargins($float, $xLeft, $yTop, $xRight, $yBottom)
    {
        // get the current float margins, for top and bottom
        $oldTop    = $this->getMargins($yTop);
        $oldBottom = $this->getMargins($yBottom);

        // update the top float margin
        if ($float=='left'  && $oldTop[0]<$xRight) {
            $oldTop[0] = $xRight;
        }
        if ($float=='right' && $oldTop[1]>$xLeft) {
            $oldTop[1] = $xLeft;
        }

        $yTop    = $this->getFloatMargeKey($yTop);
        $yBottom = $this->getFloatMargeKey($yBottom);

        // erase all the float margins that are smaller than the new one
        foreach ($this->pageMarges as $mY => $mX) {
            if ($mY<$yTop) {
                continue;
            }
            if ($mY>$yBottom) {
                break;
            }
            if ($float=='left' && $mX[0] < $xRight) {
                unset($this->pageMarges[$mY]);
            }
            if ($float=='right' && $mX[1] > $xLeft) {
                unset($this->pageMarges[$mY]);
            }
        }

        // save the new Top and Bottom margins
        $this->pageMarges[$yTop] = $oldTop;
        $this->pageMarges[$yBottom] = $oldBottom;

        // sort the margins
        ksort($this->pageMarges);
    }

    /**
     * get the Min and Max X, for Y (use the float margins)
     *
     * @param  float $y
     *
     * @return array(float, float)
     */
    public function getMargins($y)
    {
        $y = $this->getFloatMargeKey($y);

        $x = array(
            $this->pdf->getlMargin(),
            $this->pdf->getW()-$this->pdf->getrMargin()
        );

        foreach ($this->pageMarges as $mY => $mX) {
            if ($mY<=$y) {
                $x = $mX;
            }
        }

        return $x;
    }

    /**
     * Save old margins (push), and set new ones
     *
     * @param  float  $ml left margin
     * @param  float  $mt top margin
     * @param  float  $mr right margin
     *
     * @return void
     */
    public function addState($ml, $mt, $mr)
    {
        // save old margins
        $this->states[] = array(
            'l'     => $this->pdf->getlMargin(),
            't'     => $this->pdf->gettMargin(),
            'r'     => $this->pdf->getrMargin(),
            'page'  => $this->pageMarges
        );

        // set new ones
        $this->pdf->SetMargins($ml, $mt, $mr);

        // prepare for float margins
        $this->pageMarges = array();
        $this->addMargin($mt, $ml, $this->pdf->getW()-$mr);
    }

    /**
     * load the last saved margins (pop)
     *
     * @return void
     */
    public function restoreState()
    {
        $old = array_pop($this->states);
        if ($old) {
            $ml = $old['l'];
            $mt = $old['t'];
            $mr = $old['r'];
            $mP = $old['page'];
        } else {
            $ml = $this->margeLeft;
            $mt = 0;
            $mr = $this->margeRight;
            $mP = array(
                $this->getFloatMargeKey($mt) => array(
                    $ml,
                    $this->pdf->getW()-$mr
                )
            );
        }

        $this->pdf->SetMargins($ml, $mt, $mr);
        $this->pageMarges = $mP;
    }

    /**
     * get the current marge
     *
     * @return array
     */
    public function getCurrentMarge()
    {
        return array(
            'left'   => $this->margeLeft,
            'right'  => $this->margeRight,
            'top'    => $this->margeTop,
            'bottom' => $this->margeBottom,
            'marges' => $this->pageMarges,
        );
    }

    /**
     * set the current marge
     *
     * @param array $marge
     *
     * @return void
     */
    public function setCurrentMarge($marge)
    {
        $this->margeLeft    = $marge['left'];
        $this->margeRight   = $marge['right'];
        $this->margeTop     = $marge['top'];
        $this->margeBottom  = $marge['bottom'];
        $this->pageMarges   = $marge['marges'];
    }

    /**
     * reset the current marge
     *
     * @return void
     */
    public function resetCurrentMarge()
    {
        $this->margeLeft = $this->defaultLeft;
        $this->margeRight = $this->defaultRight;
        $this->margeTop = $this->defaultTop;
        $this->margeBottom = $this->defaultBottom;
        $this->pageMarges = array();
    }


    /**
     * create a new page
     *
     * @param mixed   $format
     * @param string  $orientation
     * @param array   $background background information
     * @param boolean $resetPageNumber
     * @param boolean $realPage
     *
     * @return void
     */
    public function addNewPage(
        $format = null,
        $orientation = null,
        $background = null,
        $resetPageNumber = false,
        $realPage = true
    ) {
        $this->firstPage = false;

        if (!is_null($format)) {
            $this->format = $format;
        }

        if (!is_null($orientation)) {
            $this->orientation = $orientation;
        }

        if (!is_null($background)) {
            $this->background = $format;
        }

        $this->pdf->SetMargins($this->defaultLeft, $this->defaultTop, $this->defaultRight);

        if ($resetPageNumber) {
            $this->pdf->startPageGroup();
        }

        $this->pdf->AddPage($this->orientation, $this->format);

        if ($resetPageNumber) {
            $this->pdf->myStartPageGroup();
        }

        $this->page++;

        if ($realPage) {
            $this->drawBackground();
            $this->drawPageHeader();
            $this->drawPageFooter();
        }

        $this->setMargins();
        $this->pdf->setY($this->margeTop);
    }

    /**
     * draw the background
     *
     * @return bool
     */
    protected function drawBackground()
    {
        if (!is_array($this->background)) {
            return false;
        }

        if (isset($this->background['color']) && $this->background['color']) {
            $this->pdf->setFillColorArray($this->background['color']);
            $this->pdf->Rect(0, 0, $this->pdf->getW(), $this->pdf->getH(), 'F');
        }

        if (isset($this->background['img']) && $this->background['img']) {
            $this->pdf->Image(
                $this->background['img'],
                $this->background['posX'],
                $this->background['posY'],
                $this->background['width']
            );
        }

        return true;
    }
    /**
     * draw the page header
     *
     * @return bool
     */
    protected function drawPageHeader()
    {
        return true;
    }

    /**
     * draw the page footer
     *
     * @return bool
     */
    protected function drawPageFooter()
    {
        return true;
    }
}
