<?php

namespace App\Models;

use App\Enums\TranscriptStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transcript extends Model
{
    use HasUuids, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'patient',
        'conversation',
        'end_conversation_time',
        'transcript_type_id',
        'file_size',
        'description',
        'status',
        'failure_reason',
        'audio_storage_path',
    ];

    protected $casts = [
        'conversation' => 'array',
        'file_size' => 'integer',
        'status' => TranscriptStatusEnum::class,
    ];

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }

    public function document(): HasOne 
    {
        return $this->hasOne(Document::class);
    }

    public function transcriptType(): BelongsTo
    {
        return $this->belongsTo(TranscriptType::class);
    }

    public function scopeFromUserBetweenDates(Builder $query, string $userId, Carbon $start, Carbon $end): Builder
    {
        return $query
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Usado apenas onde cota é decidida (CheckTranscriptLimit,
     * getRemainingMonthlyTranscripts) — ver decisão de cota em BE-R1-06
     * (ai-vitalfy/action-plans/shared/R1.md#sh-r1-01). Deliberadamente não
     * aplicado a `fromUserBetweenDates` diretamente: esse scope também
     * alimenta dashboard e e-mail de lembrete, que devem contar toda a
     * atividade, não só o que terminou com sucesso.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', TranscriptStatusEnum::Completed);
    }
}
