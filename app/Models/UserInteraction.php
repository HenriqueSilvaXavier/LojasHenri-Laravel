<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'produto_id',
        'tipo',
        'quantidade', // <-- ADICIONE ESTE CAMPO
        'cpf',
        'formaReceber',
        'endereco',
        'cidade',
        'estado',
        'tipoEntrega',
        'unidadeEscolhida',
        'cartao',
        'parcelamento',
        'numeroCartao',
    ];
}
?>