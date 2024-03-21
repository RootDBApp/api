<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\DraftQueries
 *
 * @property int $id
 * @property int $draft_id
 * @property string|null $queries
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Draft|null $draft
 * @method static Builder|DraftQueries newModelQuery()
 * @method static Builder|DraftQueries newQuery()
 * @method static Builder|DraftQueries query()
 * @method static Builder|DraftQueries whereCreatedAt($value)
 * @method static Builder|DraftQueries whereDraftId($value)
 * @method static Builder|DraftQueries whereId($value)
 * @method static Builder|DraftQueries whereQueries($value)
 * @method static Builder|DraftQueries whereUpdatedAt($value)
 * @mixin Eloquent
 */
class DraftQueries extends ApiModel
{
    protected $fillable = [
        'draft_id',
        'queries'
    ];

    public static array $rules = [
        'draft_id' => 'integer|exists:drafts,id',
    ];


    public function draft(): BelongsTo
    {
        return $this->belongsTo('App\Models\Draft');
    }
}
