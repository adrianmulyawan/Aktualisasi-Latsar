<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'revision_title',
        'revision_slug',
        'revision_description',
        'revision_file',
        'revision_url',
        'revision_author',
        'revision_year',
        'revision_date',
    ];

    protected $casts = [
        'revision_file' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($revision) {
            // Cek jika user_id kosong, maka set dengan ID pengguna yang sedang login
            if (!$revision->user_id) {
                $revision->user_id = auth()->id();
            }
        });
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
