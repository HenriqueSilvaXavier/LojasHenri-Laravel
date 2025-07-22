@extends('layouts.main')

@section('title', 'Página Principal')

@section('content')

<main>
    <h2>{{ $produto->nome }}</h2>

    <div id="aoLado" style="background-color: orange"> 
        <img src="/img/produtos/{{ $produto->imagem }}" alt="Imagem do produto" id="produtoDestacado">
        <div id="aoLado2">
            <p>{{ $produto->categoria }}</p>
            <p class="inline">
                @if ($produto->promocao > 0)
                    <del style="color: red;">R$ {{ number_format($produto->preco, 2, ',', '.') }}</del> 
                @endif
                R$ {{ number_format($produto->preco - ($produto->promocao / 100) * $produto->preco, 2, ',', '.') }}
            </p>
            <p class="inline">({{ $produto->promocao }}% de desconto)</p>
            <div id="inline">
                <input type="button" value="Comprar" id="comprar" onclick="abrirOverlay()">
                <input type="button" value="Retirada Rápida" id="retirada">

                @php
                    $estaFavorito = in_array($produto->id, $favoritos);
                    $estaCarrinho = in_array($produto->id, $carrinho);
                @endphp

                <input 
                    type="button" 
                    id="favoritarBotao" 
                    value="{{ $estaFavorito ? 'Desfavoritar' : 'Favoritar' }}" 
                    onclick="favoritarProduto(event, {{ $produto->id }})">

                <input 
                    type="button" 
                    id="carrinhoBotao" 
                    value="{{ $estaCarrinho ? 'Remover do Carrinho' : 'Adicionar ao Carrinho' }}" 
                    onclick="adicionarAoCarrinho(event, {{ $produto->id }})">
            </div>

            <div id="quantidade-container" style="margin-top: 10px;">
                <label for="quantidade"><strong>Quantidade:</strong></label>
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 5px;">
                    <button type="button" onclick="alterarQuantidade(-1)">−</button>
                    <input type="number" id="quantidade" name="quantidade" value="1" min="1" max="{{ $produto->estoque }}" style="width: 60px; text-align: center;" readonly>
                    <button type="button" onclick="alterarQuantidade(1)">+</button>
                    @php
                    $precoBase = $produto->promocao > 0 
                        ? $produto->preco * (1 - $produto->promocao / 100)
                        : $produto->preco;
                @endphp

                <p><strong>Total:</strong> <span id="total">R$ {{ number_format($precoBase, 2, ',', '.') }}</span></p>
                </div>

            </div>

            <p id="produtoDescricao">{{ $produto->descricao }}</p>
            <p><strong>Formas de pagamento: </strong></p>
            <div id="formasDePagamento">
                <img src="/img/hipercard.svg" alt="Símbolo do Hipercard" id="hipercard" class="formas" onclick="escolherForma(this)">
                <img src="/img/mastercard.png" alt="Símbolo do Mastercard" id="mastercard" class="formas" onclick="escolherForma(this)">
                <img src="/img//logo.png" alt="Símbolo do Cartão das Lojas Henri" id="lojasHenri" class="formas" onclick="escolherForma(this)">
                <img src="/img/pix.png" alt="Símbolo do Pix" id="pix" class="formas" onclick="escolherForma(this)">
            </div>
        </div>
    </div>
    <div id="nota-container">
        <p id="nota">
            Nota geral:
            @for($i = 1; $i <= 5; $i++)
                <span class="estrela {{ $i <= $mediaNotas ? '' : 'cinza' }}">&#9733;</span>
            @endfor
            {{ number_format($mediaNotas, 1, ',', '.') }}
        </p>
    </div>
    @if($minhaAvaliacao)
        <p id="avaliar"><ins>Editar avaliação</ins></p>
    @else
        <p id="avaliar"><ins>Avaliar o produto</ins></p>
    @endif
    <!-- Modal de Avaliação -->
    <div id="modalAvaliacao" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button type="button" class="btn-close fechar-modal" id="simboloX3" data-bs-dismiss="modal" aria-label="Close" onclick="fecharModal()"></button>

            @if($minhaAvaliacao)
                <h3>Edite sua Avaliação</h3>
            @else
                <h3>Deixe sua Avaliação</h3>
            @endif
            <form method="POST" action="{{ route('cliente.avaliar', $produto->id) }}">
                @csrf
                <label for="nota">Nota:</label>
                <div id="estrelasAvaliacao">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="estrela-avaliacao {{ $minhaAvaliacao && $i <= $minhaAvaliacao->nota ? 'selecionada' : '' }}" data-value="{{ $i }}">&#9733;</span>
                    @endfor

                </div>
                <input type="hidden" name="suaNota" id="suaNota" value="{{ $minhaAvaliacao ? $minhaAvaliacao->nota : '' }}" required>
                <br>
                <label for="comentario">Comentário:</label>
                <textarea name="comentario" id="comentario" rows="4" required>{{ $minhaAvaliacao ? $minhaAvaliacao->comentario : '' }}</textarea>

                <button type="submit" id="enviarAvaliacao">Enviar Avaliação</button>
            </form>
        </div>
    </div>

    <div id="avaliacoes" class="grid grid-cols-2">
        @foreach($avaliacoes as $avaliacao)
            <div class="notaDoUsuario">
                <p class="usuario">{{ $avaliacao->user->name }}</p>
                <p class="estrelas">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="estrela {{ $i <= $avaliacao->nota ? 'amarela' : 'cinza' }}">&#9733;</span>
                    @endfor
                </p>
                <p>{{ $avaliacao->comentario }}</p>
                <p class="data">Postado em: {{ \Carbon\Carbon::parse($avaliacao->data_avaliacao)->format('d/m/Y') }}</p>
            </div>
        @endforeach

        @if($avaliacoes->count() === 0)
            <p>Ainda não há avaliações para este produto.</p>
        @endif

        {{-- Botão ver mais pode ser usado com paginação futuramente --}}
    </div>
    <div class="d-flex justify-content-center">
        {{ $avaliacoes->links() }}
    </div>

    {{-- Avaliações e resto do conteúdo --}}
    <h2>Relacionados</h2>
    <div id="relacionados-container">        
        @foreach($relacionados as $relacionado)
            <div onclick="abrirProduto({{ $relacionado->id }})" class="relacionado-item">
                <a href="javascript:void(0)" class="block relative">
                    <img src="{{ asset('img/produtos/' . $relacionado->imagem) }}" alt="{{ $relacionado->nome }}" class="produto-imagem mx-auto mb-4" style="width: 150px; height: 150px;">
                </a>

                <!-- Ícone do carrinho -->
                <img 
                    src="{{ in_array($relacionado->id, $carrinho) ? asset('/img/carrinho2.png') : asset('img/carrinho.png') }}" 
                    alt="Adicionar ao carrinho" 
                    class="carrinho-icon-relacionados stop-click" 
                    data-id="{{ $relacionado->id }}"
                    data-favorito="{{ in_array($relacionado->id, $carrinho) ? '1' : '0' }}"
                    onclick="adicionarAoCarrinhoRelacionado(event, {{ $relacionado->id }})">

                <!-- Ícone de favorito -->
                <img 
                    src="{{ in_array($relacionado->id, $favoritos) ? asset('/img/heart2.png') : asset('img/heart.png') }}" 
                    alt="Favoritar" 
                    class="heart-icon-relacionados stop-click" 
                    data-id="{{ $relacionado->id }}"
                    data-favorito="{{ in_array($relacionado->id, $favoritos) ? '1' : '0' }}"
                    onclick="favoritarProdutoRelacionado(event, {{ $relacionado->id }})">

                <h3 class="text-lg font-semibold mb-2">{{ $relacionado->nome }}</h3>
                <p class="text-muted">{{ $relacionado->categoria }}</p>

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
                @if ($relacionado->promocao > 0)
                    @php
                        $precoPromocional = $relacionado->preco * (1 - $relacionado->promocao / 100);
                    @endphp
                    <p class="card-text text-danger mb-1">
                        <small><del>R$ {{ number_format($relacionado->preco, 2, ',', '.') }}</del></small>
                    </p>
                    <p class="card-text fw-bold">
                        R$ {{ number_format($precoPromocional, 2, ',', '.') }}
                    </p>
                @else
                    <p class="card-text">
                        R$ {{ number_format($relacionado->preco, 2, ',', '.') }}
                    </p>
                @endif
            </div>
        @endforeach
</main>

<div id="overlay" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <button type="button" class="btn-close fechar-modal" id="simboloX" data-bs-dismiss="modal" aria-label="Close"></button>
        <form id="formEntrega">
            <label for="cpf">CPF:</label>
            <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00"><br>

            <p>Como você deseja receber?</p>
            <input type="radio" id="emCasa" name="recebimento" value="emCasa">
            <label for="emCasa">Em casa</label><br>
            <input type="radio" id="retiradaRapida" name="recebimento" value="retiradaRapida">
            <label for="retiradaRapida">Retirada rápida</label><br>

            <div id="infoEndereco">
                <label for="endereco">Endereço: </label>
                <input type="text" name="endereco" id="endereco"><br>
                <label for="cidade">Cidade:</label>
                <input type="text" name="cidade" id="cidade"><br>
                <label for="estado">Estado:</label>
                <input type="text" name="estado" id="estado"><br>
                <label for="pontoDeReferencia">Ponto de referência: </label>
                <input type="text" name="pontoDeReferencia" id="pontoDeReferencia">
            </div>

            <div id="entrega">
                <p>Tipo de entrega:</p>
                <input type="radio" id="padrao" name="entrega" value="padrao">
                <label for="padrao">Padrão</label><br>
                <input type="radio" id="expresso" name="entrega" value="expresso">
                <label for="expresso">Expresso</label><br>
            </div>

            <div id="unidadeContainer" style="display: none;">
                <label for="unidade">Unidade escolhida:</label>
                <select name="unidade" id="unidade">
                </select>
            </div>

            <input class="btn btn-primary" type="button" value="Próximo" id="proximo1">
        </form>
    </div>
</div>

<!-- Overlay 2: Forma de Pagamento -->
<div id="overlay2" class="modal-overlay" style="display:none">
    <form id="formPagamento" class="modal-content">
        <button type="button" class="btn-close fechar-modal" id="simboloX2" data-bs-dismiss="modal" aria-label="Close"></button>
        <p>Formas de pagamento:</p>
        <div id="formasDePagamento">
            <img src="/img/hipercard.svg" alt="Hipercard" class="formas" onclick="escolherForma(this, 'hipercard')">
            <img src="/img/mastercard.png" alt="Mastercard" class="formas" onclick="escolherForma(this, 'mastercard')">
            <img src="/img/logo.png" alt="Lojas Henri" class="formas" onclick="escolherForma(this, 'lojasHenri')">
            <img src="/img/pix.png" alt="Pix" class="formas" onclick="escolherForma(this, 'pix')">
        </div>
        <div class="precos" id="precosHipercard" style="display:none">
            <p><strong>À vista:</strong> R$ 890,10</p>
            <label for="selectDePreco1">Parcelamento:</label>
            <select id="selectDePreco1">
                <option value="aVista">A vista</option>
            </select>
        </div>
        <div class="precos" id="precosMastercard" style="display:none">
            <p><strong>À vista:</strong> R$ 890,10</p>
            <p><strong>3x sem juros:</strong> R$ 296,70</p>
            <p><strong>6x com juros:</strong> R$ 162,84</p>
            <label for="selectDePreco2">Parcelamento:</label>
            <select id="selectDePreco2">
                <option value="aVista">A vista</option>
                <option value="3x">3x sem juros</option>
                <option value="6x">6x com juros</option>
            </select>
        </div>
        <div class="precos" id="precosLojasHenri" style="display:none">
            <p><strong>À vista:</strong> R$ 890,10</p>
            <p><strong>3x sem juros:</strong> R$ 296,70</p>
            <label for="selectDePreco3">Parcelamento:</label>
            <select id="selectDePreco3">
                <option value="aVista">A vista</option>
                <option value="3x">3x sem juros</option>
            </select>
        </div>
        <div class="precos" id="precosPix" style="display:none">
            <p><strong>À vista:</strong> R$ 890,10</p>
            <p><strong>2x sem juros:</strong> R$ 445,05</p>
            <p><strong>3x com juros:</strong> R$ 296,70</p>
            <label for="selectDePreco4">Parcelamento:</label>
            <select id="selectDePreco4">
                <option value="aVista">A vista</option>
                <option value="2x">2x sem juros</option>
                <option value="3x">3x com juros</option>
            </select>
        </div>
        <label for="numeroCartao">Número do cartão:</label>
        <input type="text" id="numeroCartao" name="numeroCartao" placeholder="0000 0000 0000 0000"><br>
        <input class="btn btn-primary" type="button" value="Anterior" id="anterior1">
        <input class="btn btn-primary" type="submit" value="Finalizar Compra" id="finalizarCompra">
    </div>
</div>

<div id="modalUnidades" class="modal-overlay" style="display: none;">
    <div class="modal-content" id="modalUnidadesContent">
        <button type="button" class="btn-close fechar-modal" data-bs-dismiss="modal" aria-label="Close" onclick="fecharModalUnidades()"></button>
        <h2>Encontre a unidade mais próxima</h2>
        
        <div class="cep-input-container">
            <label for="cepInput">Digite seu CEP:</label>
            <input type="text" id="cepInput" placeholder="00000-000" maxlength="9">
            <button type="button" id="buscarButton" onclick="buscarUnidadesProximas(event)">Buscar</button>
        </div>
        
        <div id="resultadosUnidades">
            <ul id="listaUnidades"></ul>
        </div>
    </div>
</div>

<script>
    fetch('/registrar-visualizacao', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ 
            itens: [{ produtoId: {{ $produto->id }} }]
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na resposta do servidor');
        }
        return response.json();
    })
    .then(data => {
        console.log('Resposta do servidor:', data);
    })
    .catch(error => {
        console.error('Erro ao registrar visualização:', error);
    });

    function favoritarProduto(event, id) {
        const botao = event.target;

        fetch('/toggle-favorito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ produto_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'favoritado') {
                botao.value = 'Desfavoritar';
            } else if(data.status === 'desfavoritado') {
                botao.value = 'Favoritar';
            }
        })
        .catch(err => {
            console.error('Erro ao alternar favorito:', err);
        });
    }
    function adicionarAoCarrinho(event, id) {
        event.preventDefault(); // Impede o comportamento padrão
        const botao = event.target;
                
        fetch('/toggle-carrinho', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ produto_id: id })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta da rede');
            }
            return response.json();
        })
        .then(data => {
            if(data.status === 'adicionado') {
                botao.value = 'Remover do Carrinho';
            } else if(data.status === 'removido') {
                botao.value = 'Adicionar ao Carrinho';
            }
            
            // Atualizar apenas este botão
            botao.disabled = false;
            botao.style.opacity = '1';
        })
        .catch(err => {
            console.error('Erro ao alternar carrinho:', err);
            botao.disabled = false;
            botao.style.opacity = '1';
            // Mostrar mensagem de erro ao usuário (opcional)
            alert('Ocorreu um erro. Por favor, tente novamente.');
        });
    }

    function fecharModal() {
        document.getElementById('modalAvaliacao').style.display = 'none';
    }

    // Fechar modal ao clicar fora do conteúdo
    window.onclick = function(event) {
        const modal = document.getElementById('modalAvaliacao');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    // Adicione isso no seu script
    document.getElementById('avaliar').addEventListener('click', function() {
        document.getElementById('modalAvaliacao').style.display = 'flex';
    });
    document.querySelectorAll('.estrela-avaliacao').forEach(function(estrela) {
        estrela.addEventListener('mouseover', function() {
            let valor = this.getAttribute('data-value');
            destacarEstrelas(valor);
        });

        estrela.addEventListener('mouseout', function() {
            const selecionado = document.getElementById('suaNota').value;
            destacarEstrelas(selecionado);
        });

        estrela.addEventListener('click', function() {
            let valor = this.getAttribute('data-value');
            document.getElementById('suaNota').value = valor;
            destacarEstrelas(valor);
        });
    });

    function destacarEstrelas(valor) {
        document.querySelectorAll('.estrela-avaliacao').forEach(function(estrela) {
            if (estrela.getAttribute('data-value') <= valor) {
                estrela.classList.add('selecionada');
            } else {
                estrela.classList.remove('selecionada');
            }
        });
    }
document.addEventListener('DOMContentLoaded', () => {
    const hearts = document.querySelectorAll('.heart-icon-relacionados');

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
    const carrinhos = document.querySelectorAll('.carrinho-icon-relacionados');

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

    function adicionarAoCarrinhoRelacionado(event, id) {
        event.stopPropagation(); // Impede de abrir a página
        console.log("Adicionar ao carrinho:", id);
        // Aqui você pode fazer uma requisição AJAX ou fetch:
        // fetch(`/carrinho/adicionar/${id}`, { method: 'POST' })
    }

    function favoritarProdutoRelacionado(event, id) {
        event.stopPropagation(); // Impede de abrir a página
        console.log("Favoritar produto:", id);
        // Aqui você pode fazer uma requisição AJAX ou fetch:
        // fetch(`/favoritos/toggle/${id}`, { method: 'POST' })
    }

    document.getElementById('finalizarCompra').addEventListener('click', function (e) {
        e.preventDefault();
        
        const cpf = document.getElementById('cpf').value.trim();
        const numeroCartao = document.getElementById('numeroCartao').value.trim();

        const regexCPF = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
        const regexCartao = /^\d{4} \d{4} \d{4} \d{4}$/;

        if (!regexCPF.test(cpf)) {
            alert("CPF inválido! Use o formato 000.000.000-00");
            document.getElementById('cpf').focus();
            return;
        }

        if (!regexCartao.test(numeroCartao)) {
            alert("Número do cartão inválido! Use o formato 0000 0000 0000 0000");
            document.getElementById('numeroCartao').focus();
            return;
        }

        // Remove a verificação if(confirmarCompra()) e chama diretamente
        confirmarCompra();
    });

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
                
                document.querySelectorAll('.quantidade-input').forEach(input => {
    // Ao carregar a página, calcula o total com o valor inicial
                    atualizarTotal();

                    // Escuta alterações no campo de quantidade
                    input.addEventListener('input', () => {
                        const max = parseInt(input.max);
                        const val = parseInt(input.value);

                        if (val > max) {
                            input.value = max;
                        } else if (val < 1 || isNaN(val)) {
                            input.value = 1;
                        }

                        atualizarTotal(); // Recalcula total
                    });

                    // Também escuta mudanças via setas (+/-) ou colar valor
                    input.addEventListener('change', atualizarTotal);
                });

            });
                atualizarFormasPagamento();
            document.addEventListener('DOMContentLoaded', function () {
                const overlay = document.getElementById('overlay');
                const overlay2 = document.getElementById('overlay2');
                const btnProximo1 = document.getElementById('proximo1');
                const btnAnterior1 = document.getElementById('anterior1');
                const btnFinalizar = document.getElementById('proximo2');
                const simboloX = document.getElementById('simboloX');
                const simboloX2 = document.getElementById('simboloX2');
                const retiradaRapida = document.getElementById('retiradaRapida');
                const emCasa = document.getElementById('emCasa');
                const unidadeContainer = document.getElementById('unidadeContainer');

                // Esconde o overlay2 inicialmente
                overlay2.style.display = 'none';
                unidadeContainer.style.display = 'none';

                // Mostrar unidade apenas se for retirada rápida
                function verificarRetirada() {
                    if (retiradaRapida.checked) {
                        unidadeContainer.style.display = 'block';
                    } else {
                        unidadeContainer.style.display = 'none';
                    }
                }

                retiradaRapida.addEventListener('change', verificarRetirada);
                emCasa.addEventListener('change', verificarRetirada);

                // Avançar da página de entrega para pagamento
                btnProximo1.addEventListener('click', function (e) {
                    e.preventDefault();
                    overlay.style.display = 'none';
                    overlay2.style.display = 'flex';
                });

                // Voltar da página de pagamento para entrega
                btnAnterior1.addEventListener('click', function (e) {
                    e.preventDefault();
                    overlay2.style.display = 'none';
                    overlay.style.display = 'flex';
                });

                // Fechar pop-up geral (pode adicionar lógica para fechar ambos)
                simboloX.addEventListener('click', function () {
                    overlay.style.display = 'none';
                    overlay2.style.display = 'none';
                });

                // Lógica para selecionar a forma de pagamento

            });
            function abrirOverlay() {
                document.getElementById('overlay').style.display = 'flex';
            };

            function confirmarCompra() {
                const cpfInput = document.getElementById('cpf');
                const numeroCartaoInput = document.getElementById('numeroCartao');

                const cpf = cpfInput.value.trim();
                const formaReceber = document.querySelector('input[name="recebimento"]:checked')?.value;
                const endereco = document.getElementById('endereco').value.trim();
                const cidade = document.getElementById('cidade').value.trim();
                const estado = document.getElementById('estado').value.trim();
                const pontoRef = document.getElementById('pontoDeReferencia').value.trim();
                const tipoEntrega = document.querySelector('input[name="entrega"]:checked')?.value;
                const unidadeEscolhida = formaReceber === 'retiradaRapida' ? document.getElementById('unidade').value : '';
                const cartao = document.querySelector('.formas.selected')?.alt;
                const numeroCartao = numeroCartaoInput.value.trim();
                const parcelamento = document.querySelector('.precos[style*="block"] select')?.value;

                const regexCPF = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
                const regexCartao = /^\d{4} \d{4} \d{4} \d{4}$/;

                // CPF obrigatório
                if (!regexCPF.test(cpf)) {
                    alert("CPF inválido! Use o formato 000.000.000-00");
                    cpfInput.focus();
                    return;
                }

                // Tipo de recebimento obrigatório
                if (!formaReceber) {
                    alert("Selecione como você deseja receber o pedido (em casa ou retirada rápida).");
                    return;
                }

                // Campos obrigatórios para entrega em casa
                if (formaReceber === 'emCasa') {
                    if (!endereco || !cidade || !estado || !pontoRef) {
                        alert("Preencha todos os campos de endereço para entrega em casa.");
                        return;
                    }
                }

                // Campo obrigatório se for retirada rápida
                if (formaReceber === 'retiradaRapida' && !unidadeEscolhida) {
                    alert("Selecione uma unidade para retirada rápida.");
                    return;
                }

                // Tipo de entrega obrigatório
                if (!tipoEntrega) {
                    alert("Selecione um tipo de entrega (Padrão ou Expresso).");
                    return;
                }

                // Forma de pagamento obrigatória
                if (!cartao) {
                    alert("Selecione uma forma de pagamento.");
                    return;
                }

                // Número do cartão obrigatório
                if (!regexCartao.test(numeroCartao)) {
                    alert("Número do cartão inválido! Use o formato 0000 0000 0000 0000");
                    numeroCartaoInput.focus();
                    return;
                }

                // Parcelamento obrigatório
                if (!parcelamento) {
                    alert("Selecione uma opção de parcelamento.");
                    return;
                }
                // Coleta os produtos com suas quantidades
                const itens = [{
                    produto_id: {{ $produto->id }},
                    quantidade: input.value // Usa o valor do input
                }];

                const dadosCompra = {
                    cpf,
                    formaReceber,
                    endereco,
                    cidade,
                    estado,
                    pontoDeReferencia: pontoRef,
                    tipoEntrega,
                    unidadeEscolhida,
                    cartao,
                    numeroCartao,
                    parcelamento,
                    itens
                };

                console.log('Dados enviados:', dadosCompra); // Debug
                fetch('/finalizar-compra', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(dadosCompra)
                })
                .then(response => response.json())
                .then(data => {
                    alert("Compra finalizada com sucesso!");
                    document.getElementById('overlay').style.display = 'none';
                    document.getElementById('overlay2').style.display = 'none';
                    console.log(data);
                })
                .catch(err => {
                    alert("Erro ao finalizar compra.");
                    console.error(err);
                });

                return "ok";
            }

            function escolherForma(elemento, forma) {
                document.querySelectorAll('.formas').forEach(img => img.classList.remove('selected'));

                // Adiciona 'selected' à imagem clicada
                elemento.classList.add('selected');
                document.querySelectorAll('.precos').forEach(div => div.style.display = 'none');
                document.getElementById('precos' + forma.charAt(0).toUpperCase() + forma.slice(1)).style.display = 'block';
            }

            document.getElementById('retiradaRapida').addEventListener('change', function () {
                document.getElementById('unidadeContainer').style.display = this.checked ? 'block' : 'none';
            });
            document.getElementById('emCasa').addEventListener('change', function () {
                document.getElementById('unidadeContainer').style.display = 'none';
            });

            document.getElementById("proximo1").addEventListener("click", function () {
                document.getElementById("overlay").style.display = "none";
                const overlay2 = document.getElementById("overlay2");
                overlay2.style.display = "flex"; // usar 'flex' e não 'block'!
            });

            document.getElementById('anterior1').addEventListener('click', function () {
                document.getElementById('overlay2').style.display = 'none';
                document.getElementById('overlay').style.display = 'flex';
            });
            document.getElementById('simboloX').addEventListener('click', function () {
                document.getElementById('overlay').style.display = 'none';
                document.getElementById('overlay2').style.display = 'none';
            });
            document.getElementById('simboloX2').addEventListener('click', function () {
                document.getElementById('overlay').style.display = 'none';
                document.getElementById('overlay2').style.display = 'none';
            });
            function atualizarFormasPagamento() {
                let total = 0;
                input=document.getElementById('quantidade');
                const preco = parseFloat({{($produto->promocao > 0 ? $produto->preco - ($produto->preco * ($produto->promocao / 100)) : $produto->preco)}});
                const quantidade = parseInt(input.value);
                total += preco * quantidade;
                total = parseFloat(total.toFixed(2));

                // Calcular parcelas
                const valorAVista = total;
                const valor2xSemJuros = (total / 2).toFixed(2);
                const valor3xSemJuros = (total / 3).toFixed(2);
                const valor3xComJuros = (total * 1.05 / 3).toFixed(2); // 5% juros
                const valor6xComJuros = (total * 1.10 / 6).toFixed(2); // 10% juros

                // Atualiza os valores nos blocos
                document.querySelector('#precosHipercard').innerHTML = `
                    <p><strong>À vista:</strong> R$ ${valorAVista.toFixed(2).replace('.', ',')}</p>
                    <label for="selectDePreco1">Parcelamento:</label>
                    <select id="selectDePreco1">
                        <option value="aVista">A vista</option>
                    </select>
                `;

                document.querySelector('#precosMastercard').innerHTML = `
                    <p><strong>À vista:</strong> R$ ${valorAVista.toFixed(2).replace('.', ',')}</p>
                    <p><strong>3x sem juros:</strong> R$ ${valor3xSemJuros.replace('.', ',')}</p>
                    <p><strong>6x com juros:</strong> R$ ${valor6xComJuros.replace('.', ',')}</p>
                    <label for="selectDePreco2">Parcelamento:</label>
                    <select id="selectDePreco2">
                        <option value="aVista">A vista</option>
                        <option value="3x">3x sem juros</option>
                        <option value="6x">6x com juros</option>
                    </select>
                `;

                document.querySelector('#precosLojasHenri').innerHTML = `
                    <p><strong>À vista:</strong> R$ ${valorAVista.toFixed(2).replace('.', ',')}</p>
                    <p><strong>3x sem juros:</strong> R$ ${valor3xSemJuros.replace('.', ',')}</p>
                    <label for="selectDePreco3">Parcelamento:</label>
                    <select id="selectDePreco3">
                        <option value="aVista">A vista</option>
                        <option value="3x">3x sem juros</option>
                    </select>
                `;

                document.querySelector('#precosPix').innerHTML = `
                    <p><strong>À vista:</strong> R$ ${valorAVista.toFixed(2).replace('.', ',')}</p>
                    <p><strong>2x sem juros:</strong> R$ ${valor2xSemJuros.replace('.', ',')}</p>
                    <p><strong>3x com juros:</strong> R$ ${valor3xComJuros.replace('.', ',')}</p>
                    <label for="selectDePreco4">Parcelamento:</label>
                    <select id="selectDePreco4">
                        <option value="aVista">A vista</option>
                        <option value="2x">2x sem juros</option>
                        <option value="3x">3x com juros</option>
                    </select>
                `;
            }

            function atualizarTotalProduto() {
    const input = document.getElementById('quantidade');
    const quantidade = parseInt(input.value);

    // Valores vindos do Blade
    const precoBase = {{ $produto->promocao > 0 
        ? number_format($produto->preco * (1 - $produto->promocao / 100), 2, '.', '') 
        : number_format($produto->preco, 2, '.', '') 
    }};

    const total = precoBase * quantidade;
    
    const totalFormatado = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    document.getElementById('total').textContent = totalFormatado;
}

function alterarQuantidade(delta) {
    const input = document.getElementById('quantidade');
    const max = parseInt(input.max);
    const min = parseInt(input.min);
    let valorAtual = parseInt(input.value);

    valorAtual += delta;

    if (valorAtual < min) valorAtual = min;
    if (valorAtual > max) valorAtual = max;

    input.value = valorAtual;
    atualizarTotalProduto(); // Atualiza total ao mudar quantidade
}

document.addEventListener('DOMContentLoaded', () => {
    atualizarTotalProduto(); // Ao carregar
    document.getElementById('quantidade').addEventListener('change', atualizarTotalProduto);
});

const unidades = [
  { nome: "São Paulo", cep: "01001-000", endereco: "Praça da Sé, 1 - Sé" },
  { nome: "Rio de Janeiro", cep: "20040-020", endereco: "Av. Rio Branco, 1 - Centro" },
  { nome: "Belo Horizonte", cep: "30110-012", endereco: "Av. Afonso Pena, 1212 - Centro" },
  { nome: "Brasília", cep: "70040-010", endereco: "SBS Quadra 2, 1 - Asa Sul" },
  { nome: "Salvador", cep: "40015-200", endereco: "Rua Chile, 1 - Centro" },
  { nome: "Recife", cep: "50030-230", endereco: "Av. Dantas Barreto, 1 - São José" },
  { nome: "Porto Alegre", cep: "90010-320", endereco: "Praça Marechal Deodoro, 1 - Centro Histórico" },
  { nome: "Curitiba", cep: "80010-000", endereco: "Rua Barão do Rio Branco, 1 - Centro" },
  { nome: "Manaus", cep: "69005-070", endereco: "Av. Eduardo Ribeiro, 1 - Centro" },
  { nome: "Fortaleza", cep: "60010-270", endereco: "Rua Senador Pompeu, 1 - Centro" },
  { nome: "Goiânia", cep: "74003-010", endereco: "Av. Goiás, 1 - Centro" },
  { nome: "Belém", cep: "66010-120", endereco: "Av. Presidente Vargas, 1 - Campina" },
  { nome: "Campinas", cep: "13013-000", endereco: "Rua José Paulino, 1 - Centro" },
  { nome: "Ribeirão Preto", cep: "14010-060", endereco: "Av. Jerônimo Gonçalves, 1 - Centro" },
  { nome: "Santos", cep: "11010-260", endereco: "Av. Ana Costa, 1 - Gonzaga" },
];

document.getElementById('retirada').addEventListener('click', function() {
    document.getElementById('modalUnidades').style.display = 'flex';
});

// Função para fechar o modal de unidades
function fecharModalUnidades() {
    document.getElementById('modalUnidades').style.display = 'none';
}

// Formatação do CEP
document.getElementById('cepInput').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length > 5) {
        value = value.substring(0, 5) + '-' + value.substring(5, 8);
    }
    
    e.target.value = value;
});

// Função para buscar unidades próximas
// Função para buscar unidades próximas
async function buscarUnidadesProximas(event) {
    event.preventDefault();
    
    const cep = document.getElementById('cepInput').value;
    
    if (!/^\d{5}-\d{3}$/.test(cep)) {
        alert('Por favor, digite um CEP válido no formato 00000-000');
        return;
    }
    
    try {
        // Mostrar loading
        document.getElementById('listaUnidades').innerHTML = '<li>Buscando unidades próximas...</li>';
        
        // Obter coordenadas do CEP informado
        const coordCliente = await getCoordinates(cep);
        
        if (!coordCliente) {
            throw new Error('Não foi possível obter as coordenadas do CEP');
        }

        // Calcular distância para cada unidade
        const unidadesComDistancia = [];
        
        for (const unidade of unidades) {
            const coordUnidade = await getCoordinates(unidade.cep);
            if (coordUnidade) {
                const distancia = getDistance(
                    coordCliente.lat, 
                    coordCliente.lon, 
                    coordUnidade.lat, 
                    coordUnidade.lon
                );
                unidadesComDistancia.push({ ...unidade, distancia });
            }
        }
        
        // Ordenar por distância e pegar as 5 mais próximas
        const maisProximas = unidadesComDistancia
            .sort((a, b) => a.distancia - b.distancia)
            .slice(0, 5);
        
        exibirUnidades(maisProximas);
        
    } catch (error) {
        console.error('Erro ao buscar unidades:', error);
        document.getElementById('listaUnidades').innerHTML = 
            '<li>Erro ao buscar unidades. Tente novamente.</li>';
    }
}

// Função para exibir as unidades no modal
function exibirUnidades(unidades) {
    unidadesCarregadas = true;
    const lista = document.getElementById('listaUnidades');
    lista.innerHTML = '';
    lista.style.display = 'block';
    unidades.forEach(unidade => {
        const item = document.createElement('li');
        item.innerHTML = `
            <strong>${unidade.nome}</strong><br>
            ${unidade.endereco}<br>
            <small>Distância: ${unidade.distancia.toFixed(1)} km - CEP: ${unidade.cep}</small>
        `;
        
        lista.appendChild(item);
    });
}

// Funções auxiliares para cálculo de distância (simulação)
let unidadesCarregadas = false; // assume inicialmente como falso

async function getCoordinates(cep) {
    try {
        const viaCepResponse = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const viaCepData = await viaCepResponse.json();

        if (viaCepData.erro) {
            throw new Error('CEP inválido ou não encontrado');
        }

        const address = `${viaCepData.logradouro}, ${viaCepData.bairro}, ${viaCepData.localidade}, ${viaCepData.uf}`;
        console.log('Buscando coordenadas para:', cep, address);

        const geoResponse = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&addressdetails=1&limit=1`);

        if (!geoResponse.ok) {
            const erroTexto = await geoResponse.text();
            console.error('Resposta da API com erro:', erroTexto);
            throw new Error('Erro ao geocodificar endereço');
        }

        const geoData = await geoResponse.json();
        console.log('Dados geocodificados:', geoData);

        if (!geoData || geoData.length === 0) {
            throw new Error('Endereço não encontrado no mapa');
        }

        return {
            lat: parseFloat(geoData[0].lat),
            lon: parseFloat(geoData[0].lon),
            address: address
        };

    } catch (error) {
        console.error('Erro no getCoordinates:', error);

        // Espera 30 segundos para mostrar o alerta, se unidades ainda não tiverem sido carregadas
        setTimeout(() => {
            if (!unidadesCarregadas) {
                alert('Não foi possível determinar a localização. Verifique o CEP e tente novamente.');
            }
        }, 30000);

        return null;
    }
}

function getDistance(lat1, lon1, lat2, lon2) {
    // Fórmula de Haversine para calcular distância entre coordenadas
    const R = 6371; // Raio da Terra em km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; // Distância em km
}

const select = document.getElementById("unidade");
unidades.forEach((unidade, index) => {
    const option = document.createElement("option");
    option.value = index;
    option.textContent = `${unidade.endereco} - ${unidade.nome}`;
    select.appendChild(option);
});

</script>

@endsection
