<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_to',
        'document_id',
        'request_document_title',
        'notes',
        'is_internal_document',
        'file',
        'url',
        'author',
        'date',
        'status',
    ];

    protected $casts = [
        'file' => 'array',
        'is_internal_document' => 'boolean',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
