<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jawaban extends Model
{
    use HasFactory;

    protected $table = 'jawaban';
    protected $primaryKey = 'ans_id';

    protected $fillable = [
        'qa_id',
        'user_id',
        'isi',
        'nilai',
    ];

    public function pasanganQa()
    {
        return $this->belongsTo(PasanganQa::class, 'qa_id', 'qa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
