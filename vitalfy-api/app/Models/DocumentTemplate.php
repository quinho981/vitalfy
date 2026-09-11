<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'document_templates';

    protected $fillable = [
        'name',
        'category_id',
        'content',
        'description',
        'sections',
    ];

    protected $casts = [
        'sections' => 'array',
    ];

    /**
     * BE-R23-03 (ai-vitalfy/action-plans/backend/R23.md): sections e o
     * contrato interno de montagem do documento -- GET /templates
     * (DocumentTemplateController::index()) devolve o model inteiro, e sem
     * isto vazaria a estrutura de 55 documentos para qualquer usuario
     * autenticado.
     */
    protected $hidden = ['sections'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplateCategory::class, 'category_id');
    }
}
