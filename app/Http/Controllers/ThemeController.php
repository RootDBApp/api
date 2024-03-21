<?php

namespace App\Http\Controllers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ThemeController extends Controller
{
    private string $_themes_directory = __DIR__.'/../../../frontend-themes/';

    public function index(string $theme): string
    {
        return file_get_contents($this->_themes_directory . $theme . '/theme.css');
    }

    public function fonts(string $fontName): string
    {
        $it = new RecursiveDirectoryIterator($this->_themes_directory);
        foreach (new RecursiveIteratorIterator($it) as $file) {
            if (strstr($file, $fontName)) {

                return file_get_contents($file);
            }
        }

        abort(404);
    }
}
