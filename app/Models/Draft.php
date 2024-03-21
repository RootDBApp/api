<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Draft
 *
 * @property int $id
 * @property int $user_id
 * @property int $conf_connector_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $User
 * @property-read ConfConnector|null $confConnector
 * @property-read Collection|DraftQueries[] $draftQueries
 * @property-read int|null $draft_queries_count
 * @method static Builder|Draft newModelQuery()
 * @method static Builder|Draft newQuery()
 * @method static Builder|Draft query()
 * @method static Builder|Draft whereConfConnectorId($value)
 * @method static Builder|Draft whereCreatedAt($value)
 * @method static Builder|Draft whereId($value)
 * @method static Builder|Draft whereName($value)
 * @method static Builder|Draft whereUpdatedAt($value)
 * @method static Builder|Draft whereUserId($value)
 * @mixin Eloquent
 */
class Draft extends ApiModel
{
    protected $fillable = [
        'user_id',
        'conf_connector_id',
        'name'
    ];

    public static array $rules = [
        'user_id'           => 'integer|exists:user,id',
        'conf_connector_id' => 'integer|exists:conf_connectors,id',
        'name'              => 'required|between:1,100',
    ];

    public function User(): HasOne
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function confConnector(): HasOne
    {
        return $this->hasOne('App\Models\ConfConnector', 'id', 'conf_connector_id');
    }

    public function draftQueries(): HasMany
    {
        return $this->hasMany('App\Models\DraftQueries');
    }
}
