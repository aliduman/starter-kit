<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** İşlem geçmişi kaydı (App\Models\Concerns\LogsActivity yazar). */
class Activity extends Model
{
    public const UPDATED_AT = null;

    public const EVENTS = [
        'created' => 'Eklendi',
        'updated' => 'Değiştirildi',
        'deleted' => 'Silindi',
    ];

    protected $table = 'activity_log';

    protected $fillable = ['user_id', 'event', 'subject_type', 'subject_id', 'changes'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public static function record(string $event, Model $subject, array $changes = []): void
    {
        static::create([
            'user_id' => auth()->id(),
            'event' => $event,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'changes' => $changes ?: null,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
