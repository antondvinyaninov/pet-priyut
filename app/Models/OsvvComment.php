<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsvvComment extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового присваивания
     */
    protected $fillable = [
        'osvv_request_id',
        'user_id',
        'comment',
    ];

    /**
     * Получить заявку, к которой относится комментарий
     */
    public function osvvRequest(): BelongsTo
    {
        return $this->belongsTo(OsvvRequest::class);
    }

    /**
     * Получить пользователя, оставившего комментарий
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
