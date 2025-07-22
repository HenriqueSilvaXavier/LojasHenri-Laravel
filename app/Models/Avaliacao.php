<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'produto_id', 'nota', 'comentario', 'data_avaliacao'
    ];

    protected $table = 'avaliacoes'; // <- corrige o nome da tabela

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}