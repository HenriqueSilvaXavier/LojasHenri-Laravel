@extends('layouts.main')

@section('title', 'Painel Administrativo')

@section('content')
<style>
    body.painel-admin {
        display: inline-block;
        width: 100%;
    }
    #campo{
        max-width: 140px;
    }
    @media (max-width: 256px) {
        header{
            justify-content: center !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('painel-admin');

        const header = document.getElementById('header');
        if (header) {
            header.style.flexDirection = 'row';
            header.style.justifyContent = 'space-between';
        }
    });
</script>

<div class="container">
    <h2 class="mb-4">Painel Administrativo</h2>

    <a href="{{ route('produtos.create') }}" class="btn btn-success mb-3">Novo Produto</a>

    @if(session('mensagem'))
        <div class="alert alert-success">{{ session('mensagem') }}</div>
    @endif

    <form method="GET" action="{{ route('admin') }}" id="search-form" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
        <select name="campo" class="form-select" id="campo">
            <option value="nome" {{ request('campo') == 'nome' ? 'selected' : '' }}>Nome</option>
            <option value="preco" {{ request('campo') == 'preco' ? 'selected' : '' }}>Preço</option>
            <option value="categoria" {{ request('campo') == 'categoria' ? 'selected' : '' }}>Categoria</option>
            <option value="promocao" {{ request('campo') == 'promocao' ? 'selected' : '' }}>Promoção</option>
            <option value="fim_promocao" {{ request('campo') == 'fim_promocao' ? 'selected' : '' }}>Fim da Promoção</option>
            <option value="estoque" {{ request('campo') == 'estoque' ? 'selected' : '' }}>Estoque</option>
        </select>

        <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}"
            class="form-control w-auto">

        <button type="submit" id="search-button" class="btn btn-warning text-white">Buscar</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Promoção</th>
                    <th>Fim da Promoção</th>
                    <th>Categoria</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produtos as $produto)
                    <tr>
                        <td>{{ $produto->id }}</td>
                        <td>{{ $produto->nome }}</td>
                        <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                        <td>{{ $produto->promocao }}%</td>
                        <td>
                            @if($produto->fim_promocao)
                                {{ \Carbon\Carbon::parse($produto->fim_promocao)->format('d/m/Y H:i:s') }}
                            @else
                                Sem data de fim de promoção
                            @endif
                        </td>
                        <td>{{ $produto->categoria }}</td> {{-- Relacionamento: $produto->categoria->nome --}}
                        <td>{{ $produto->estoque }}</td>
                        <td class="d-flex gap-2">
                            <a href="{{ route('produtos.edit', $produto->id) }}" class="btn btn-info">Editar</a>
                            <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Deletar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Nenhum produto cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $produtos->links() }}
    </div>
</div>
@endsection
