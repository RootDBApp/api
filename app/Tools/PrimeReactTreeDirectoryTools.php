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

use App\Models\Directory;
use App\Models\PrimeReactTreeDirectory;
use Illuminate\Database\Eloquent\Collection;

class PrimeReactTreeDirectoryTools
{
    private Collection $collection;
    /** @var PrimeReactTreeDirectory[] $prime_react_tree */
    private array $prime_react_tree = [];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return Collection
     */
    public function getPrimeReactTree(): Collection
    {
        $this->prime_react_tree[] = new PrimeReactTreeDirectory(
            999999,
            trans('favorites'),
            null,
            999999,
            'pi pi-fw pi-star',
            null,
        );

        /** @var Directory $directory */
        foreach ($this->collection->all() as $directory) {

            if ($directory->parent_id !== null) {

                foreach ($this->prime_react_tree as $item) {

                    $this->_recursiveSearch($item, $directory);
                }
            } else {

                $this->prime_react_tree[] = new PrimeReactTreeDirectory(
                    $directory->id,
                    $directory->name,
                    null,
                    $directory->id,
                    'pi pi-fw pi-folder',
                    null,
                );
            }
        }

        return new Collection($this->prime_react_tree);
    }

    private function _recursiveSearch(PrimeReactTreeDirectory $item, Directory $directory)
    {
        if ((int)$item->key === (int)$directory->parent_id) {

            if ($item->children === null) {

                $item->children = [];
            }

            $item->children[] = new PrimeReactTreeDirectory(
                $directory->id,
                $directory->name,
                $item->key,
                $directory->id,
                'pi pi-fw pi-folder',
                null,
            );

        } else if (is_array($item->children)) {

            foreach ($item->children as $item2) {

                $this->_recursiveSearch($item2, $directory);
            }
        }
    }
}
