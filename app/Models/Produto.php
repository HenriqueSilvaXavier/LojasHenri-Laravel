<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'descricao',
        'imagem',
        'preco',
        'promocao',
        'fim_promocao',
        'categoria',
        'estoque'
    ];
    protected $casts = [
        'fim_promocao' => 'datetime',
    ];

    public function avaliacoes(){
        return $this->hasMany(Avaliacao::class);
    }
}
