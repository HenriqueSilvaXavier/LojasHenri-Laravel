@extends('layouts.main')

@section('title', 'Painel Administrativo')

@section('content')
<div class="container">
    <h2 class="mb-4">Painel Administrativo</h2>

    <a href="{{ route('produtos.create') }}" class="btn btn-success mb-3">Novo Produto</a>

    @if(session('mensagem'))
        <div class="alert alert-success">{{ session('mensagem') }}</div>
    @endif
    <form method="GET" action="{{ route('admin') }}" id="search-form" class="mb-4 flex flex-wrap items-center gap-2">
        <select name="campo" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <option value="nome" {{ request('campo') == 'nome' ? 'selected' : '' }}>Nome</option>
            <option value="preco" {{ request('campo') == 'preco' ? 'selected' : '' }}>Preço</option>
            <option value="categoria" {{ request('campo') == 'categoria' ? 'selected' : '' }}>Categoria</option>
            <option value="promocao" {{ request('campo') == 'promocao' ? 'selected' : '' }}>Promoção</option>
            <option value="estoque" {{ request('campo') == 'estoque' ? 'selected' : '' }}>Estoque</option>
        </select>

        <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}"
            class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 w-full sm:w-auto">

        <button type="submit" id="search-button" class="px-4 py-2 bg-yellow text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">Buscar</button>
    </form>


    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Promoção</th>
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
                    <td>{{ $produto->promocao}}%</td>
                    <td>{{ $produto->categoria }}</td> {{-- Ajuste aqui se for relacionamento --}}
                    <td>{{ $produto->estoque }}</td>
                    <td class="d-flex">
                        <a href="/admin/produtos/{{ $produto->id }}/edit" class="btn btn-info edit-btn"> Editar</a> 
                        <form action="/admin/produtos/{{ $produto->id }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger delete-btn"> Deletar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhum produto cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $produtos->links() }}
    </div>
</div>
<script>
    function ajustarLarguraBody() {
        if (window.innerWidth <= 575) {
            document.body.style.width = 'fit-content';
        } else {
            document.body.style.width = '100%';
        }
    }

    // Executa ao carregar a página
    ajustarLarguraBody();

    // Atualiza ao redimensionar
    window.addEventListener('resize', ajustarLarguraBody);
</script>
@endsection
