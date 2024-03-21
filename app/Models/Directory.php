<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Directory
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int|null $parent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $organization_id
 * @property-read Organization|null $organization
 * @method static Builder|Directory newModelQuery()
 * @method static Builder|Directory newQuery()
 * @method static Builder|Directory query()
 * @method static Builder|Directory whereDescription($value)
 * @method static Builder|Directory whereId($value)
 * @method static Builder|Directory whereName($value)
 * @method static Builder|Directory whereCreatedAt($value)
 * @method static Builder|Directory whereUpdatedAt($value)
 * @method static Builder|Directory whereParentId($value)
 * @method static Builder|Directory whereOrganizationId($value)
 * @mixin Eloquent
 */
class Directory extends ApiModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'organization_id',
        'parent_id',
        'description',
    ];

    public static array $rules = [
        'name'            => 'required|between:2,255',
        'organization_id' => 'integer|exists:organizations,id',
        'parent_id'       => 'nullable|integer|exists:directories,id',
        'description'     => ''
    ];

    public function organization(): HasOne
    {
        return $this->hasOne('App\Models\Organization');
    }
}
