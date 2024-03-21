<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Group
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Organization|null $organization
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group query()
 * @method static Builder|Group whereCreatedAt($value)
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereName($value)
 * @method static Builder|Group whereUpdatedAt($value)
 * @method static Builder|Category whereOrganizationId($value)
 * @property int $organization_id
 * @mixin Eloquent
 */
class Group extends ApiModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'organization_id',
    ];

    public static array $rules = [
        'name'            => 'required|between:2,255',
        'organization_id' => 'integer|exists:organizations,id',
    ];

    public function organization(): HasOne
    {
        return $this->hasOne('App\Models\Organization');
    }
}
