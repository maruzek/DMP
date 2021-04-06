<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

// Vlastní rozšíření Twigu
// Automaticky rozezává linky v příspěvku

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('anchor', [$this, 'formatAnchor']),
        ];
    }

    public function formatAnchor($text)
    {
        $reg_exUrl = '#(?<!href\=[\'"])(https?|ftp|file)://[-A-Za-z0-9+&@\#/%()?=~_|$!:,.;]*[-A-Za-z0-9+&@\#/%()=~_|$]#';

        if (preg_match_all($reg_exUrl, $text, $url)) {
            $replace = [];
            $regexArray = [];
            for ($i = 0; $i < count($url[0]); $i++) {
                array_push($regexArray, $url[0][$i]);
                array_push($replace, '<a href="' . $url[0][$i] . '">' . $url[0][$i] . '</a>');
            }

            $text = str_replace($regexArray, $replace, $text);
        }

        return $text;
    }
}
