<?php
/**
 * Color object for Securimage CAPTCHA
 *
 * @since 2.0
 * @package Securimage
 * @subpackage classes
 *
 */
namespace Itoris\RegFields\Helper\Captcha\Securimage;

class Securimagecolor {
    /**
     * Red component: 0-255
     *
     * @var int
     */
    var $r;
    /**
     * Green component: 0-255
     *
     * @var int
     */
    var $g;
    /**
     * Blue component: 0-255
     *
     * @var int
     */
    var $b;

    /**
     * Create a new Securimagecolor object.<br />
     * Specify the red, green, and blue components using their HTML hex code equivalent.<br />
     * Example: The code for the HTML color #4A203C is:<br />
     * $color = new Securimagecolor(0x4A, 0x20, 0x3C);
     *
     * @param $red Red component 0-255
     * @param $green Green component 0-255
     * @param $blue Blue component 0-255
     */
    function __construct($red, $green = null, $blue = null)
    {
        if ($green == null && $blue == null && preg_match('/^#[a-f0-9]{3,6}$/i', $red)) {
            $col = substr($red, 1);
            if (strlen($col) == 3) {
                $red   = str_repeat(substr($col, 0, 1), 2);
                $green = str_repeat(substr($col, 1, 1), 2);
                $blue  = str_repeat(substr($col, 2, 1), 2);
            } else {
                $red   = substr($col, 0, 2);
                $green = substr($col, 2, 2);
                $blue  = substr($col, 4, 2); 
            }
            
            $red   = hexdec($red);
            $green = hexdec($green);
            $blue  = hexdec($blue);
        } else {
            if ($red < 0) $red       = 0;
            if ($red > 255) $red     = 255;
            if ($green < 0) $green   = 0;
            if ($green > 255) $green = 255;
            if ($blue < 0) $blue     = 0;
            if ($blue > 255) $blue   = 255;
        }

        $this->r = $red;
        $this->g = $green;
        $this->b = $blue;
    }
}
