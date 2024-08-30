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

namespace App\Models;

use App\Enums\EnumAssetSource;
use App\Enums\EnumStorageType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Enum;

/**
 *
 * @property int $id
 * @property string $name
 * @property EnumStorageType $storage_type
 * @property string|null $data_content When storage type = database
 * @property int $organization_id
 * @property string|null $pathname When storage type = filesystem
 * @property string|null $url When storage type = online
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Asset newModelQuery()
 * @method static Builder|Asset newQuery()
 * @method static Builder|Asset query()
 * @method static Builder|Asset whereCreatedAt($value)
 * @method static Builder|Asset whereData($value)
 * @method static Builder|Asset whereId($value)
 * @method static Builder|Asset whereName($value)
 * @method static Builder|Asset whereOrganizationId($value)
 * @method static Builder|Asset wherePathname($value)
 * @method static Builder|Asset whereStorageType($value)
 * @method static Builder|Asset whereUpdatedAt($value)
 * @method static Builder|Asset whereUrl($value)
 * @mixin Eloquent
 */
class Asset extends ApiModel
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'organization_id',
        'storage_type',
        'pathname',
        'url',
        'data_content',
    ];

    public static function rules(): array
    {
        return [
            'name'            => 'required|between:2,255',
            'organization_id' => 'integer|exists:organizations,id',
            'storage_type'    => [new Enum(EnumStorageType::class)],
        ];
    }

    protected $casts = [
        'storage_type' => EnumStorageType::class,
    ];
}
