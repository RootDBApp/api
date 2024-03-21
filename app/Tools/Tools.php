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

namespace App\Tools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Tools
{
    public static function array_cartesian_utm(array $arrays)
    {
        $cartesic = [];

        // Calculate expected size of cartesian array...
        $size = (sizeof($arrays) > 0) ? 1 : 0;
        foreach ($arrays as $key_name => $value) {

            if (!is_array($value)) {

                $arrays[$key_name] = [$value];
                $size = $size * sizeof([$value]);
            } else {

                $size = $size * sizeof($value);
            }
        }

        $size_of_params = sizeof($arrays);

        for ($num_combination = 0; $num_combination < $size; $num_combination++) {

            for ($j = 0; $j < $size_of_params; $j++) {

                $key = array_keys($arrays)[$j];

                $current = current($arrays[$key]);
                $cartesic[$num_combination][$key] = $current;
            }

            // Set cursor on next element in the parameter's values array, beginning with the last parameter.
            for ($j = ($size_of_params - 1); $j >= 0; $j--) {

                $key = array_keys($arrays)[$j];

                // If next returns true, then break.
                if (next($arrays[$key])) {

                    break;
                } // If next returns false, then reset and go on with previous parameter.
                else {

                    reset($arrays[$key]);
                }
            }
        }

        return $cartesic;
    }

    public static function getRandomChars(int $length): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ&é"(-è_çà)=$*!:;,?./§µ%+°';
        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    public static function getHttpFrom(Request $request): string|bool
    {

        $http_security_from = $request->headers->get('security-from');
        $http_referer = $request->headers->get('referer');
        $http_origin = $request->headers->get('origin');

        $http_from = '';
        if (mb_strlen($http_security_from) > 3) {

            $http_from = $http_security_from;
        } else if (mb_strlen($http_referer) > 3) {

            $http_from = $http_referer;
        } else if (mb_strlen($http_origin) > 3) {

            $http_from = $http_origin;
        } else {

            Log::warning('Unable to know where this request comes from.');
            return false;
        }

        return $http_from;
    }

    public static function formatBytes($size, $precision = 2): string
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    public static function recCopy($objectSrc): mixed
    {
        if (is_string($objectSrc)) {
            return str_replace('SOME_NEVER_OCCURING_VALUE_145645645734534523', 'XYZ', $objectSrc);
        }

        if (is_numeric($objectSrc)) {
            return ($objectSrc + 0);
        }

        if (is_bool($objectSrc)) {
            return (bool)$objectSrc;
        }
        if (is_null($objectSrc)) {
            return null;
        }

        if (is_object($objectSrc)) {
            $new = (object)array();
            foreach ($objectSrc as $key => $val) {
                $new->$key = self::recCopy($val);
            }
            return $new;
        }

        if (!is_array($objectSrc)) {
            print_r(gettype($objectSrc) . "\n");
            return $objectSrc;
        }

        $new = array();

        foreach ($objectSrc as $key => $val) {
            $new[$key] = self::recCopy($val);
        }

        return $new;
    }
}
