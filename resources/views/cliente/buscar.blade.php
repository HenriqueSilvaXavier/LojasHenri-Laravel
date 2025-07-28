@extends('layouts.main')

@section('title', "Busca: $busca")

@section('content')
<div class="container mx-auto px-4 py-6">
    <h2 class="text-2xl font-bold mb-6">Resultados da busca: "{{ $busca }}"</h2>

    <div id="grid-container" class="grid grid-cols-3 grid-rows-3 gap-6 justify-items-center">
        @forelse ($produtos as $produto)
            <div class="grid-itens !bg-orange border-2 border-orange-500 rounded-lg overflow-hidden shadow hover:shadow-lg transition duration-200 block relative p-4 max-w-xs" onclick="abrirProduto({{ $produto->id }})">
                <div class="img-container">
                    @if ($produto->estoque < 1)
                        <span class="esgotado-categ badge bg-danger position-absolute m-2">Esgotado</span>
                    @endif
                    <img src="{{ asset('img/produtos/' . $produto->imagem) }}" alt="{{ $produto->nome }}" class="produto-imagem mx-auto mb-4">
                    <!-- Ícone do carrinho -->
                    @if ($produto->estoque > 0)
                    <img
                        src="{{ in_array($produto->id, $carrinho) ? asset('/img/carrinho2.png') : asset('img/carrinho.png') }}"
                        alt="Adicionar ao carrinho"
                        class="carrinho-icon-categ stop-click"
                        data-id="{{ $produto->id }}"
                        data-favorito="{{ in_array($produto->id, $carrinho) ? '1' : '0' }}"
                        onclick="adicionarAoCarrinho(event, {{ $produto->id }})">
                    @endif
                    <!-- Ícone de favorito -->
                    <img
                        src="{{ in_array($produto->id, $favoritos) ? asset('/img/heart2.png') : asset('img/heart.png') }}"
                        alt="Favoritar"
                        class="heart-icon-categ stop-click"
                        data-id="{{ $produto->id }}"
                        data-favorito="{{ in_array($produto->id, $favoritos) ? '1' : '0' }}"
                        onclick="favoritarProduto(event, {{ $produto->id }})">
                </div>

                <h3 class="text-lg font-semibold mb-2">{{ $produto->nome }}</h3>
                <p class="text-muted">{{ $produto->categoria }}</p>

                @php
                    $media = round($produto->avaliacoes_avg_nota ?? 0);
                    $totalAvaliacoes = $produto->avaliacoes_count ?? 0;
                @endphp

                <div class="mb-2">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="estrela {{ $i <= $media ? 'amarela' : '' }}">&#9733;</span>
                    @endfor
                    <span class="avaliacoes-text">({{ $totalAvaliacoes }})</span>
                </div>
                @php
                    $fim = $produto->fim_promocao ? \Carbon\Carbon::parse($produto->fim_promocao) : null;
                @endphp

                @if ($produto->promocao > 0 && (!$fim || $fim->isFuture()))
                    @php
                        $precoPromocional = $produto->preco * (1 - $produto->promocao / 100);
                        $fim = $produto->fim_promocao ? \Carbon\Carbon::parse($produto->fim_promocao) : null;
                    @endphp

                    <div class="precoEDesconto">
                        <p class="card-text text-danger mb-1">
                            <small><del>R$ {{ number_format($produto->preco, 2, ',', '.') }}</del></small>
                        </p>
                        <p class="card-text">
                            R$ {{ number_format($precoPromocional, 2, ',', '.') }}
                        </p>
                    </div>

                    @if ($fim && $fim->isFuture())
                        <p id="timer-{{ $produto->id }}" 
                            data-fim-promocao="{{ $produto->fim_promocao }}"
                            class="contador-promo px-4 py-2 rounded-lg text-sm mt-3 inline-block shadow-sm">
                        </p>
                    @endif
                @else
                    <p class="card-text">
                        R$ {{ number_format($produto->preco, 2, ',', '.') }}
                    </p>
                @endif
            </div>

        @empty
            <p class="col-span-3 text-gray-600">Nenhum produto encontrado para "{{ $busca }}".</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $produtos->links() }}
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const hearts = document.querySelectorAll('.heart-icon-categ');

        hearts.forEach(icon => {
            const heartFilled = '{{ asset('/img/heart2.png') }}';
            const heartEmpty = '{{ asset('/img/heart.png') }}';

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
                const isFavorito = icon.dataset.favorito === '1';

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
        const carrinhos = document.querySelectorAll('.carrinho-icon-categ');

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

document.addEventListener('DOMContentLoaded', () => {
    const timers = document.querySelectorAll('[id^="timer-"]');

    timers.forEach(timer => {
        const fimPromocao = new Date(timer.dataset.fimPromocao).getTime();
        function atualizarContador() {
            const agora = new Date().getTime();
            const diferenca = fimPromocao - agora;

            if (diferenca <= 0) {
                timer.textContent = "Promoção encerrada";
                clearInterval(intervalo);
                return;
            }

            const dias = Math.floor(diferenca / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diferenca % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutos = Math.floor((diferenca % (1000 * 60 * 60)) / (1000 * 60));
            const segundos = Math.floor((diferenca % (1000 * 60)) / 1000);

            timer.textContent = `Termina em ${dias}d ${horas}h ${minutos}m ${segundos}s`;
        }

        atualizarContador();
        const intervalo = setInterval(atualizarContador, 1000);
    });
});
    </script>
</div>
@endsection
