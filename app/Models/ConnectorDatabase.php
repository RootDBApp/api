<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;


/**
 * App\Models\ConnectorDatabase
 *
 * @property int $id
 * @property string $name
 * @property bool $available
 * @method static Builder|ConnectorDatabase newModelQuery()
 * @method static Builder|ConnectorDatabase newQuery()
 * @method static Builder|ConnectorDatabase query()
 * @method static Builder|ConnectorDatabase whereId($value)
 * @method static Builder|ConnectorDatabase whereName($value)
 * @method static Builder|ConnectorDatabase whereAvailable($value)
 * @property-read ConfConnector|null $confConnector
 * @mixin Eloquent
 */
class ConnectorDatabase extends Model
{

    public function confConnector(): HasOne
    {
        return $this->hasOne('App\Models\ConfConnector');
    }
}
