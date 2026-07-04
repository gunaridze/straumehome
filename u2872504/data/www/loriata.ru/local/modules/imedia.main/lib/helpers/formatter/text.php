<?php
namespace Imedia\Main\Helpers\Formatter;

class Text
{
    /**
     * @param string $string
     * @param array $noStrip
     * @return string
     */
    public static function toCamelCase(string $string, array $noStrip = []): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $string);
        $string = trim($string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        $string = lcfirst($string);

        return $string;
    }

    /**
     * @param string $str
     * @param string $encoding
     * @return string
     */
    public static function mb_ucfirst(string $str, string $encoding = 'UTF-8'): string
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding) .
            mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }
}