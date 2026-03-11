<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeksBacaan extends Model
{
    use HasFactory;

    protected $table = 'teks_bacaan';
    protected $primaryKey = 'text_id';

    protected $fillable = [
        'judul',
        'isi',
    ];

    public function pasanganQa()
    {
        return $this->hasMany(PasanganQa::class, 'text_id', 'text_id');
    }
}
