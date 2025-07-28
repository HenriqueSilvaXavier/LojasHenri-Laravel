@extends('layouts.main')

@section('title', 'Página Principal')

@section('content')
    <h2 id="titulo-carrossel" class="mb-4">Melhores Promoções</h2>
    <div id="carouselExampleControls" class="carousel slide mb-5" data-bs-ride="carousel" data-bs-interval="2000">
        <div class="carousel-inner">
            @foreach ($carrosselProdutos as $index => $produto)
            <div onclick="window.location.href='/produto/{{ $produto->id }}'" class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <div class="text-center">
                <img 
                    class="d-block w-100" 
                    src="/img/produtos/{{ $produto->imagem }}" 
                    alt="{{ $produto->nome }}" 
                    style="max-height: 400px; object-fit: cover;">
                @php
                    $fim = $produto->fim_promocao ? \Carbon\Carbon::parse($produto->fim_promocao) : null;
                @endphp

                @if ($produto->promocao > 0 && (!$fim || $fim->isFuture()))
                    <div class="carousel-caption bg-dark bg-opacity-50 rounded p-2">
                        <h5 class="text-white">{{ $produto->nome }}</h5>
                        <span class="badge bg-danger">-{{ $produto->promocao }}%</span>
                        
                        {{-- Contador ao vivo --}}
                        @if ($fim && $fim->isFuture())
                            <p 
                                class="contador-promocao" 
                                data-fim="{{ $fim->toIso8601String() }}" 
                                id="contador-carrossel-{{ $produto->id }}"
                            ></p>
                        @endif
                    </div>
                @else
                    <div class="carousel-caption bg-dark bg-opacity-50 rounded p-2">
                        <h5 class="text-white">{{ $produto->nome }}</h5>
                    </div>
                @endif

                </div>
            </div>
            @endforeach
        </div>

        <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Anterior</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Próximo</span>
        </a>
    </div>

    <h2 id="titulo-produtos">Recomendações Personalizadas</h2>
    <div id="produtos" class="scroll-container">
        @forelse($recomendados as $recomendado)
            <div class="col-md-3 mb-4 produto-item" onclick="abrirProduto({{ $recomendado->id }})">
                <div class="card h-100">
                    <div class="produto-container">
                        <img src="{{ asset('img/produtos/' . $recomendado->imagem) }}" 
                            alt="{{ $produto->nome }}" 
                            class="produto-img">

                        <!-- Ícone Carrinho -->
                        <img 
                            src="{{ in_array($recomendado->id, $carrinho) ? asset('img/carrinho2.png') : asset('img/carrinho.png') }}" 
                            alt="Adicionar ao carrinho" 
                            class="carrinho-icon stop-click" 
                            data-id="{{ $recomendado->id }}"
                            onclick="adicionarAoCarrinho(event, {{ $recomendado->id }})">

                        <!-- Ícone Favorito -->
                        <img 
                            src="{{ in_array($recomendado->id, $favoritos) ? asset('img/heart2.png') : asset('img/heart.png') }}" 
                            alt="Favoritar" 
                            class="heart-icon stop-click" 
                            data-id="{{ $recomendado->id }}"
                            onclick="favoritarProduto(event, {{ $recomendado->id }})">
                    </div>

                    <div class="card-body text-center">
                        <h5 class="card-title">{{ $recomendado->nome }}</h5>
                        <p class="text-muted">{{ $recomendado->categoria }}</p>
                        @php
                            $media = round($recomendado->avaliacoes_avg_nota ?? 0);
                            $totalAvaliacoes = $recomendado->avaliacoes_count ?? 0;
                        @endphp

                        <div class="mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="estrela {{ $i <= $media ? 'amarela' : '' }}">&#9733;</span>
                            @endfor
                            <span class="avaliacoes-text">({{ $totalAvaliacoes }})</span>
                        </div>

                        @php
                            $fim = $recomendado->fim_promocao ? \Carbon\Carbon::parse($recomendado->fim_promocao) : null;
                        @endphp

                        @if ($recomendado->promocao > 0 && (!$fim || $fim->isFuture()))
                            @php
                                $precoPromocional = $recomendado->preco * (1 - $recomendado->promocao / 100);
                                $fim = $recomendado->fim_promocao ? \Carbon\Carbon::parse($recomendado->fim_promocao) : null;
                            @endphp

                            <p class="card-text text-danger mb-1">
                                <small><del>R$ {{ number_format($recomendado->preco, 2, ',', '.') }}</del></small>
                            </p>
                            <p class="card-text">
                                R$ {{ number_format($precoPromocional, 2, ',', '.') }}
                            </p>

                            @if ($fim && $fim->isFuture())
                                <p 
                                    class="small mb-0 contador-promocao"
                                    data-fim="{{ $fim->toIso8601String() }}"
                                    id="contador-recomendado-{{ $recomendado->id }}"
                                ></p>
                            @endif
                        @else
                            <p class="card-text">
                                R$ {{ number_format($recomendado->preco, 2, ',', '.') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        @empty
            <p>Sem recomendações no momento.</p>
        @endforelse
    </div>

    <h2 class="mt-5 text-center">🔥 Em alta hoje</h2>
    <div class="emAlta-container">
        @foreach ($produtosEmAltaHoje as $produto)
            <div class="col-md-3 mb-4 produto-item" onclick="abrirProduto({{ $produto->id }})">
                <div class="card h-100 text-center">
                    <div class="produto-container">
                        <img src="{{ asset('img/produtos/' . $produto->imagem) }}" 
                            alt="{{ $produto->nome }}" 
                            class="produto-img">
                        
                        @if ($produto->estoque < 1)
                            <span class="esgotado badge bg-danger position-absolute top-0 start-0 m-2">Esgotado</span>
                        @endif

                        @if ($produto->estoque > 0)
                        <img 
                            src="{{ in_array($produto->id, $carrinho) ? asset('img/carrinho2.png') : asset('img/carrinho.png') }}" 
                            alt="Adicionar ao carrinho" 
                            class="carrinho-icon stop-click" 
                            data-id="{{ $produto->id }}"
                            onclick="adicionarAoCarrinho(event, {{ $produto->id }})">
                        @endif

                        <img 
                            src="{{ in_array($produto->id, $favoritos) ? asset('img/heart2.png') : asset('img/heart.png') }}" 
                            alt="Favoritar" 
                            class="heart-icon stop-click" 
                            data-id="{{ $produto->id }}"
                            data-favorito="{{ in_array($produto->id, $favoritos) ? '1' : '0' }}"
                            onclick="favoritarProduto(event, {{ $produto->id }})">
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">{{ $produto->nome }}</h5>
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
                                $agora = \Carbon\Carbon::now();
                                $fim = $produto->fim_promocao ? \Carbon\Carbon::parse($produto->fim_promocao) : null;
                                $tempoRestante = $fim && $fim->isFuture() ? $fim->diffForHumans($agora, ['parts' => 2, 'short' => true]) : null;
                            @endphp
                            <p class="card-text text-danger mb-1">
                                <small><del>R$ {{ number_format($produto->preco, 2, ',', '.') }}</del></small>
                            </p>
                            <p class="card-text">
                                R$ {{ number_format($precoPromocional, 2, ',', '.') }}
                            </p>
                            @if ($fim && $fim->isFuture())
                                <p 
                                    class="small mb-0 contador-promocao" 
                                    data-fim="{{ $fim->toIso8601String() }}"
                                    id="contador-{{ $produto->id }}"
                                ></p>
                            @endif

                        @else
                            <p class="card-text">
                                R$ {{ number_format($produto->preco, 2, ',', '.') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

        @endforeach
    </div>
<script>

document.addEventListener('DOMContentLoaded', () => {
    const hearts = document.querySelectorAll('.heart-icon');

    hearts.forEach(icon => {
        const heartFilled = '{{ asset('img/heart2.png') }}';
        const heartEmpty = '{{ asset('img/heart.png') }}';

        // Trocar imagem ao passar o mouse
        icon.addEventListener('mouseenter', () => {
            icon.src = heartFilled;
        });

        icon.addEventListener('mouseleave', () => {
            if (icon.dataset.favorito === '0') {
                icon.src = heartEmpty;
            }
        });

        // Clique para favoritar/desfavoritar
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
    const carrinhos = document.querySelectorAll('.carrinho-icon');

    carrinhos.forEach(icon => {
        const carrinhoFilled = '{{ asset('img/carrinho2.png') }}';
        const carrinhoEmpty = '{{ asset('img/carrinho.png') }}';
        
        // Usando let para permitir reatribuição
        let isInCart = icon.src.includes('carrinho2.png');
        
        // Hover - só muda se não estiver no carrinho
        icon.addEventListener('mouseenter', () => {
            if (!isInCart) {
                icon.src = carrinhoFilled;
            }
        });

        icon.addEventListener('mouseleave', () => {
            if (!isInCart) {
                icon.src = carrinhoEmpty;
            }
        });

        // Remove o listener de clique do HTML já que vamos tratar via JS
        icon.removeAttribute('onclick');
        
        // Clique para adicionar/remover carrinho
        icon.addEventListener('click', (event) => {
            event.stopPropagation();
            const produtoId = icon.dataset.id;
            
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
                    isInCart = true; // Agora pode ser modificado
                } else {
                    icon.src = carrinhoEmpty;
                    isInCart = false;
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar carrinho:', error);
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
    const contadores = document.querySelectorAll('.contador-promocao');

    contadores.forEach(contador => {
        const fim = new Date(contador.dataset.fim);

        function atualizarContador() {
            const agora = new Date();
            const diff = fim - agora;

            if (diff <= 0) {
                contador.textContent = "Promoção encerrada";
                return;
            }

            const dias = Math.floor(diff / (1000 * 60 * 60 * 24));
            const horas = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const minutos = Math.floor((diff / (1000 * 60)) % 60);
            const segundos = Math.floor((diff / 1000) % 60);

            contador.textContent = `Termina em ${dias}d ${horas}h ${minutos}m ${segundos}s`;
        }

        atualizarContador();
        setInterval(atualizarContador, 1000);
    });
});

</script>

@endsection