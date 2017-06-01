#!/usr/bin/php
<?php

require_once "vendor/autoload.php";

use Goutte\Client;

if (!function_exists('out')) {
    function out($strMessage, $strColor = '')
    {
        $arrColors = [
            'black' => '30',
            'blue' => '34',
            'green' => '32',
            'red' => '31'
        ];
        if ($strColor == '') {
            echo $strMessage . PHP_EOL;
        }
        $strColorPicker = $arrColors[$strColor];
        echo "\033[" . $strColorPicker . "m " . $strMessage . " \033[0m \n" . PHP_EOL;
    }
}

if (!function_exists('ie')) {
    function ie($index, $arr, $default = null)
    {
        if (isset($arr[$index])) {
            return $arr[$index];
        }
        return $default;
    }
}

if (!function_exists('usage')) {
    function usage()
    {
        echo "Usage: ./ready2web https://www.google.pt " . PHP_EOL;
    }
}

if (!function_exists('robots')) {
    function robots($site)
    {
        $strSite = rtrim($site, "/") . "/robots.txt";
        $content = file_get_contents($strSite);
        return $content !== '';
    }
}

if (!function_exists('sitemap')) {
    function sitemap($site)
    {
        $strSite = rtrim($site, "/") . "/sitemap.xml";
        $content = file_get_contents($strSite);
        return $content !== '';
    }
}

if (!function_exists('favicon')) {
    function favicon($site)
    {
        $client = new Client();
        $crawler = $client->request('GET', $site);
        $favicon = $crawler->filter('link[rel="shortcut icon"]');

        return $favicon->count() > 0;
    }
}

if (!function_exists('ga')) {
    function ga($site)
    {
        // @credit: http://www.tatvic.com/blog/php-code-to-check-existence-of-google-analytics-on-a-webpage-and-extract-ua-id/
        $options = array(
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => true, // don't return headers
            CURLOPT_ENCODING => "", // handle all encodings
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64)", // who am i
            CURLOPT_SSL_VERIFYHOST => false, //ssl verify host
            CURLOPT_SSL_VERIFYPEER => false, //ssl verify peer
            CURLOPT_NOBODY => false,
        );

        $ch = curl_init($site);
        curl_setopt_array($ch, $options);

        //2> Grab content of the url using CURL
        $content = curl_exec($ch);

        $flag1_trackpage = false; //FLag for the phrase '_trackPageview'
        $flag2_ga_js = false; //FLag for the phrase 'ga.js'

        // Script Regex
        $script_regex = "/<script\b[^>]*>([\s\S]*?)<\/script>/i";

        // UA_ID Regex
        $ua_regex = "/UA-[0-9]{5,}-[0-9]{1,}/";

        // Preg Match for Script
        //3> Extract all the script tags of the content
        preg_match_all($script_regex, $content, $inside_script);

        //4> Check for ga.gs and _trackPageview in all <script> tag
        /*for ($i = 0; $i < count($inside_script[0]); $i++) {
            if (stristr($inside_script[0][$i], "ga.js")) {
                $flag2_ga_js = true;
            }
            /*if (stristr($inside_script[0][$i], "_trackPageview")) {
                $flag1_trackpage = true;
            }
        }*/

        // Preg Match for UA ID
        //5> Extract UA-ID using regular expression
        preg_match_all($ua_regex, $content, $ua_id);

        //6> Check whether all 3 word phrases are present or not.
        if (/*$flag2_ga_js && $flag1_trackpage &&*/ count($ua_id > 0)) {
            return $ua_id;
        } else {
            return false;
        }
    }
}

$arg = $argv;

// Not provide site
if (!$strSite = ie(1, $arg, false)) {
    usage();
    die();
}

out("Site " . $strSite, 'green');

// Check if exists robots.txt
if (!robots($strSite)) {
    out("Robots not found on site", 'red');
} else {
    out("Robots ok", 'green');
}

// Check if exists sitemap.xml
if (!sitemap($strSite)) {
    out("Sitemap not found on site", 'red');
} else {
    out("Sitemap ok", 'green');
}

$strIdGa = ga($strSite);
// Check if exists Google Analytics
if (!$strIdGa) {
    out("Google analytics not found", 'red');
} else {
    out("Google analytics ok", 'green');
    out("Google analytics ids: \n", 'blue');
    out(print_r($strIdGa, true), 'blue');
}

// Check if exists Microdata (any type)

// Check if exists favicon.ico
if (!favicon($strSite)) {
    out("Favicon not found on site", 'red');
} else {
    out("Favicon ok", 'green');
}


