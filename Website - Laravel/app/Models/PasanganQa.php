<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasanganQa extends Model
{
    use HasFactory;

    protected $table = 'pasangan_qa';
    protected $primaryKey = 'qa_id';

    protected $fillable = [
        'text_id',
        'pertanyaan',
        'jawaban_guru',
    ];

    public function teksBacaan()
    {
        return $this->belongsTo(TeksBacaan::class, 'text_id', 'text_id');
    }

    public function jawaban()
    {
        return $this->hasMany(Jawaban::class, 'qa_id', 'qa_id');
    }
}
