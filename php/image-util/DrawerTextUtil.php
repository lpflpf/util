<?php
/**
 * Drawer.php
 *
 * @author   lipengfei
 * @created  2017/8/14 11:27
 */


/**
 * Class Drawer
 *  生成画笔工具,绘制文本
 *
 * @package App\Providers\ImageProcess
 */
class DrawerTextUtil
{
    private $_text;
    private $_x = 0;
    private $_y = 0;
    private $_font;
    private $_fontColor = '#000000';
    private $_strokeColor;

    // 设置字体大小
    private $_fontSize = 24;

    private $_textDecoration = \Imagick::DECORATION_NO;

    // 字数
    private $_rowWidthByWord = 1000;
    // 像素
    private $_rowHeightByPix = 51;

    private $_limitLineNumber = 1000;

    private $_textAlign = \Imagick::ALIGN_LEFT;


    private $_drawer;

    /**
     * DrawerTextUtil constructor.
     *
     * @param string      $text
     * @param             $x
     * @param             $y
     * @param ImagickDraw $drawer
     */
    public function __construct(string $text, $x, $y, \ImagickDraw $drawer = null)
    {
        $this->_x = $x;
        $this->_y = $y;
        $this->_text = $text;
        $this->_drawer = $drawer;
        // set default font
        $this->_font = "ping-fang.ttf";
    }

    /**
     * 设置文本的水平坐标
     *
     * @param int $x
     *
     * @return $this
     */
    public function setX(int $x)
    {
        $this->_x = $x;
        return $this;
    }

    /**
     * 设置文本的垂直坐标
     *
     * @param int $y
     *
     * @return $this
     */
    public function setY(int $y)
    {
        $this->_y = $y;
        return $this;
    }

    /**
     * 设置文本信息
     *
     * @param $text
     *
     * @return $this
     */
    public function setText(string $text)
    {
        $this->_text = $text;
        return $this;
    }

    /**
     * 控制文本的宽度，通过设置行数量，选择是折行，还是做省略号替换
     *
     * @param int $wordWidth
     *
     * @see DrawerTextUtil::setMaxLineNumber()
     * @return $this
     */
    public function setRowWidth(int $wordWidth)
    {
        $this->_rowWidthByWord = $wordWidth;
        return $this;
    }

    /**
     * 设置行间距
     *
     * @param $rowHeight
     *
     * @return $this
     */
    public function setRowHeight($rowHeight)
    {
        $this->_rowHeightByPix = $rowHeight;
        return $this;
    }

    /**
     * 设置字体，默认为苹方字体
     * @param string $font
     *
     * @return $this
     */
    public function setFont(string $font)
    {
        $this->_font = $font;
        return $this;
    }

    /**
     * 设置字号，默认为24号
     * @param int $fontSize
     *
     * @return $this
     */
    public function setFontSize(int $fontSize)
    {
        $this->_fontSize = $fontSize;
        return $this;
    }

    /**
     * 设置字体颜色
     * @param string $color
     *
     * @return $this
     */
    public function setFontColor(string $color)
    {
        $this->_fontColor = $color;
        return $this;
    }

    /**
     * 设置边颜色
     * @param string $color
     *
     * @return $this
     */
    public function setStrokeColor(string $color)
    {
        $this->_strokeColor = $color;
        return $this;
    }

    /**
     * 设置文本的线条装饰
     * @param int $type
     */
    public function setDecoration(int $type)
    {
        $this->_textDecoration = $type;
    }

    /**
     * 设置行最多的数量
     *
     * @param int $maxLineNumber
     *
     * @return DrawerTextUtil
     */
    public function setMaxLineNumber(int $maxLineNumber)
    {
        $this->_limitLineNumber = $maxLineNumber;
        return $this;
    }

    /**
     * 绘制图片
     * @return ImagickDraw
     */
    public function draw()
    {
        if ($this->_drawer == null) {
            $this->_drawer = new \ImagickDraw();
        }
        $this->_drawer->setFont($this->_font);
        $this->_drawer->setFillColor(new ImagickPixel($this->_fontColor));
        $this->_drawer->setFontSize($this->_fontSize);
        $this->_drawer->setTextDecoration($this->_textDecoration);
        $this->_drawer->setTextAlignment($this->_textAlign);

        if ($this->_strokeColor != null) {
            $this->_drawer->setStrokeColor(new ImagickPixel($this->_strokeColor));
        }

        $currentRow = 0;
        $stringArr = self::cutWords2Array($this->_text, $this->_rowWidthByWord);

        if (count($stringArr) > $this->_limitLineNumber) {
            $stringArr = array_slice($stringArr, 0, $this->_limitLineNumber);
            $stringArr[$this->_limitLineNumber - 1] = mb_strimwidth($stringArr[$this->_limitLineNumber - 1] . ' ', 0, $this->_rowWidthByWord * 2, '…');
        }
        foreach ($stringArr as $rowText) {
            $this->_drawer->annotation($this->_x, $this->_y + $currentRow * $this->_rowHeightByPix, $rowText);
            $currentRow += 1;
        }

        return $this->_drawer;
    }

    /**
     * 将字符按照等宽切为数组
     *
     * @return array
     */
    public static function cutWords2Array($words, $width)
    {
        $results = [];
        $words = strip_tags($words);
        $words = str_replace(array("\t", "\n", "\r"), array("", "", "", ""), $words);
        $totalWidth = mb_strwidth($words, 'utf-8') / 2;
        $splitCount = ceil(floatval($totalWidth) / $width);
        $begin = 0;

        for ($i = 0; $i < $splitCount; $i++) {
            $substr = mb_strimwidth($words, $begin , $width * 2);
            $begin = $begin + mb_strlen($substr);
            $results [] = $substr;
        }

        return $results;
    }

    /**
     * 文本对齐方式
     * 1 : left
     * 2 : center
     * 3 : right
     *
     * @param int $type
     */
    public function setAlign(int $type)
    {
        $this->_textAlign = $type;
    }
}