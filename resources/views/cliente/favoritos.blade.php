@extends('layouts.main')

@section('title', "Favoritos")

@section('content')
    <h2>Favoritos</h2>

    {{-- Mostrar esta mensagem apenas se não houver produtos favoritos --}}
    @if ($produtos->isEmpty())
        <p id="explicacao" style="display:block;">Parece que você não colocou nenhum item nos favoritos</p>
    @else
        <p id="explicacao" style="display:none;">Parece que você não colocou nenhum item nos favoritos</p>
    @endif

    <div id="favoritos-container">
        <ul>
            @forelse ($produtos as $produto)
                <li id="produto{{ $produto->id }}" class="produto-item" style="position: relative; border: 1px solid brown; margin: 10px; padding: 10px; width: 200px; flex: 0 0 auto;">
                    <a href="{{ url('produto/' . $produto->id) }}">
                        <img src="{{ asset('/img/produtos/' . $produto->imagem) }}" 
                            alt="{{ $produto->nome }}" 
                            class="img-produto-favorito" />
                    </a>
                    <h3>{{ $produto->nome }}</h3>
                    <p class="text-muted">{{ $produto->categoria }}</p>

                    {{-- Ícone do carrinho --}}
                    @if ($produto->promocao > 0)
                        @php
                            $precoPromocional = $produto->preco * (1 - $produto->promocao / 100);
                        @endphp
                        <p class="card-text text-danger mb-1">
                            <small><del>R$ {{ number_format($produto->preco, 2, ',', '.') }}</del></small>
                        </p>
                        <p class="card-text fw-bold">
                            R$ {{ number_format($precoPromocional, 2, ',', '.') }}
                        </p>
                    @else
                        <p>R$ {{ number_format($produto->preco, 2, ',', '.') }}</p>
                    @endif

                    @if ($produto->estoque < 1)
                        <span class="esgotado-fav badge bg-danger position-absolute m-2">Esgotado</span>
                    @endif

                    @if ($produto->estoque > 0)
                    <img 
                        src="{{ in_array($produto->id, $carrinho) ? asset('img/carrinho2.png') : asset('img/carrinho.png') }}" 
                        alt="Adicionar ao carrinho" 
                        class="carrinho-icon-fav stop-click" 
                        data-id="{{ $produto->id }}"
                        data-favorito="{{ in_array($produto->id, $carrinho) ? '1' : '0' }}"
                        onclick="adicionarAoCarrinho(event, {{ $produto->id }})">

                    @endif
                    
                    {{-- Ícone de favorito --}}
                    <img 
                        src="{{ in_array($produto->id, $favoritos) ? asset('img/heart2.png') : asset('img/heart.png') }}" 
                        alt="Favoritar" 
                        class="heart-icon-fav stop-click" 
                        data-id="{{ $produto->id }}"
                        data-favorito="{{ in_array($produto->id, $favoritos) ? '1' : '0' }}"
                        onclick="favoritarProduto(event, {{ $produto->id }})">
                </li>
            @empty
                {{-- Nenhum produto favorito --}}
            @endforelse
        </ul>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const hearts = document.querySelectorAll('.heart-icon-fav');

                hearts.forEach(icon => {
                    const heartFilled = '{{ asset('img/heart2.png') }}';
                    const heartEmpty = '{{ asset('img/heart.png') }}';

                    icon.addEventListener('mouseenter', () => {
                        icon.src = heartFilled;
                    });

                    icon.addEventListener('mouseleave', () => {
                        if (icon.dataset.favorito === '0') {
                            icon.src = heartEmpty;
                        }
                    });

                    icon.addEventListener('click', () => {
                        const produtoId = icon.dataset.id;

                        fetch('/toggle-favorito', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ produto_id: produtoId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'favoritado') {
                                icon.src = heartFilled;
                                icon.dataset.favorito = '1';
                            } else {
                                icon.src = heartEmpty;
                                icon.dataset.favorito = '0';
                            }
                        });
                    });
                });
                const carrinhos = document.querySelectorAll('.carrinho-icon-fav');

                carrinhos.forEach(icon => {
                    const carrinhoFilled = '{{ asset('img/carrinho2.png') }}';
                    const carrinhoEmpty = '{{ asset('img/carrinho.png') }}';

                    // Hover
                    icon.addEventListener('mouseenter', () => {
                        icon.src = carrinhoFilled;
                    });

                    icon.addEventListener('mouseleave', () => {
                        if (icon.dataset.favorito === '0') {
                            icon.src = carrinhoEmpty;
                        }
                    });

                    // Clique para adicionar/remover carrinho
                    icon.addEventListener('click', () => {
                        const produtoId = icon.dataset.id;
                        const isCarrinho = icon.dataset.favorito === '1';

                        fetch('/toggle-carrinho', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ produto_id: produtoId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'adicionado') {
                                icon.src = carrinhoFilled;
                                icon.dataset.favorito = '1';
                            } else {
                                icon.src = carrinhoEmpty;
                                icon.dataset.favorito = '0';
                            }
                        });
                    });
                });
            });
            function abrirProduto(id) {
        window.location.href = `/produto/${id}`;
    }

    function adicionarAoCarrinho(event, id) {
        event.stopPropagation(); // Impede de abrir a página
        console.log("Adicionar ao carrinho:", id);
        // Aqui você pode fazer uma requisição AJAX ou fetch:
        // fetch(`/carrinho/adicionar/${id}`, { method: 'POST' })
    }

    function favoritarProduto(event, id) {
        event.stopPropagation(); // Impede de abrir a página
        console.log("Favoritar produto:", id);
        // Aqui você pode fazer uma requisição AJAX ou fetch:
        // fetch(`/favoritos/toggle/${id}`, { method: 'POST' })
    }
        </script>
    </div>
@endsection
