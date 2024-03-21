<?php
/*
 * This file is part of RootDB.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * AUTHORS
 * PORQUET Sébastien <sebastien.porquet@ijaz.fr>
 */

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
