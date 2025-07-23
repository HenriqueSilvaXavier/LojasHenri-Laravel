@extends('layouts.main')

@section('title', "Carrinho")

@section('content')
    <h2>Carrinho</h2>

    {{-- Mostrar esta mensagem apenas se não houver produtos favoritos --}}
    @if ($produtos->isEmpty())
        <p id="explicacao" style="display:block;">Parece que você não colocou nenhum item no Carrinho</p>
    @else
        <p id="explicacao" style="display:none;">Parece que você não colocou nenhum item nos Carrinho</p>
    @endif

    <div id="carrinho-container">
        <ul>
            @php
                $total = 0;
            @endphp
            @forelse ($produtos as $produto)
                <li id="produto{{ $produto->id }}" 
                    style="position: relative; border: 1px solid brown; margin: 10px; padding: 10px; width: 200px; flex: 0 0 auto; cursor: pointer;" 
                    data-id="{{ $produto->id }}"
                >
                    <a href="javascript:void(0)">
                        <img src="{{ asset('/img/produtos/' . $produto->imagem) }}" 
                            alt="{{ $produto->nome }}" 
                            class="img-produto-carrinho" />
                    </a>

                    <h3>{{ $produto->nome }}</h3>

                    @php
                        $precoPromocional = $produto->promocao > 0 
                            ? $produto->preco * (1 - $produto->promocao / 100) 
                            : $produto->preco;
                        $total += $precoPromocional;
                    @endphp

                    @if ($produto->promocao > 0)
                        <p class="card-text text-danger mb-1">
                            <small><del>R$ {{ number_format($produto->preco, 2, ',', '.') }}</del></small>
                        </p>
                    @endif

                    <p class="card-text fw-bold">
                        R$ {{ number_format($precoPromocional, 2, ',', '.') }}
                    </p>

                    <!-- Ícone do carrinho -->
                    @if ($produto->estoque < 1)
                        <span class="esgotado-fav badge bg-danger position-absolute m-2">Esgotado</span>
                    @endif
                    <img 
                        src="{{ in_array($produto->id, $carrinho) ? asset('img/carrinho2.png') : asset('img/carrinho.png') }}" 
                        alt="Adicionar ao carrinho" 
                        class="carrinho-icon-fav stop-click" 
                        data-id="{{ $produto->id }}"
                        data-favorito="{{ in_array($produto->id, $carrinho) ? '1' : '0' }}"
                        onclick="adicionarAoCarrinho(event, {{ $produto->id }})">

                    <!-- Ícone de favorito -->
                    <img 
                        src="{{ in_array($produto->id, $favoritos) ? asset('img/heart2.png') : asset('img/heart.png') }}" 
                        alt="Favoritar" 
                        class="heart-icon-fav stop-click" 
                        data-id="{{ $produto->id }}"
                        data-favorito="{{ in_array($produto->id, $favoritos) ? '1' : '0' }}"
                        onclick="favoritarProduto(event, {{ $produto->id }})">

                    <div>
                        <label for="quantidade{{ $produto->id }}">Quantidade:</label>
                        <input type="number" 
                            class="quantidade-input stop-click" 
                            id="quantidade{{ $produto->id }}" 
                            name="quantidade[{{ $produto->id }}]"
                            min="0" 
                            max="{{ $produto->estoque }}" 
                            value="0"
                            data-id="{{ $produto->id }}"
                            data-preco="{{ $produto->promocao > 0 ? $precoPromocional : $produto->preco }}">
                    </div>
                </li>

            @empty
                {{-- Nenhum produto favorito --}}
            @endforelse
        </ul>
    </div>
    <div style="margin-top: 20px; text-align: center;">
        <h3 id="total-compra">Total da Compra: R$ 0,00</h3>
        <button class="btn btn-success" id="abrirOverlay">Finalizar Compra</button>
    </div>
    <!-- Modal de Finalização de Compra -->
     <!-- Overlay 1: Endereço e Tipo de Entrega -->
<div id="overlay" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <button class="fechar-modal" id="simboloX"><strong>X</strong></button>
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
        <button class="fechar-modal" id="simboloX2"><strong>X</strong></button>
        <p>Formas de pagamento:</p>
        <div id="formasDePagamento">
            <img src="/img/hipercard.svg" id="hipercard" alt="Hipercard" class="formas" onclick="escolherForma(this, 'hipercard')">
            <img src="/img/mastercard.png" id="mastercard" alt="Mastercard" class="formas" onclick="escolherForma(this, 'mastercard')">
            <img src="/img/logo.png" id="lojasHenri" alt="Lojas Henri" class="formas" onclick="escolherForma(this, 'lojasHenri')">
            <img src="/img/pix.png" id="pix" alt="Pix" class="formas" onclick="escolherForma(this, 'pix')">
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
 <script>
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

                confirmarCompra();
                alert("Compra finalizada com sucesso!");
                document.getElementById('overlay').style.display = 'none';
                document.getElementById('overlay2').style.display = 'none';
                // Aqui você pode adicionar o envio dos dados para o servidor
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
                    input.addEventListener('click', function(event) {
                        event.stopPropagation(); // Impede que o clique no input redirecione para a página
                    });
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
            function atualizarTotal() {
                let total = 0;
                document.querySelectorAll('.quantidade-input').forEach(input => {
                    const preco = parseFloat(input.dataset.preco);
                    const quantidade = parseInt(input.value);
                    const subtotal = preco * quantidade;

                    total += subtotal;

                    const subtotalElement = document.getElementById('subtotal' + input.id.replace('quantidade', ''));
                    if (subtotalElement) {
                        subtotalElement.textContent = 'Subtotal: R$ ' + subtotal.toFixed(2).replace('.', ',');
                    }
                });

                document.getElementById('total-compra').textContent = 'Total da Compra: R$ ' + total.toFixed(2).replace('.', ','); // total+0.04.toFixed(5).replace('.', ',');
                
                atualizarFormasPagamento();
            }

            atualizarTotal(); // inicializa o total

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

                // Confirmar finalização

                // Lógica para selecionar a forma de pagamento
                function escolherForma(elemento, forma) {
                    // Remove 'selected' de todas as imagens
                    document.querySelectorAll('.formas').forEach(img => img.classList.remove('selected'));

                    // Adiciona 'selected' à imagem clicada
                    elemento.classList.add('selected');

                    // Esconde todas as divs de preços
                    document.querySelectorAll('.precos').forEach(div => div.style.display = 'none');

                    // Mostra a div de preços correspondente
                    const idPrecos = 'precos' + forma.charAt(0).toUpperCase() + forma.slice(1);
                    document.getElementById(idPrecos).style.display = 'block';
                }
            });
            document.getElementById('abrirOverlay').addEventListener('click', function () {
                document.getElementById('overlay').style.display = 'flex';
            });
            document.getElementById('proximo1').addEventListener('click', function () {
                document.getElementById('overlay2').style.display = 'flex';
            });


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
                const itens = Array.from(document.querySelectorAll('.quantidade-input')).map(input => ({
                    produto_id: input.id.replace('quantidade', ''),
                    quantidade: input.value
                }));

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

                console.log(dadosCompra); // Debug
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

                return "ok"
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
                document.querySelectorAll('.quantidade-input').forEach(input => {
                    const preco = parseFloat(input.dataset.preco);
                    const quantidade = parseInt(input.value);
                    total += preco * quantidade;
                });

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
    
    document.querySelectorAll('#carrinho-container li').forEach(item => {
    item.addEventListener('click', function () {
        const id = this.dataset.id;
        window.location.href = `/produto/${id}`;
    });
});

document.querySelectorAll('.stop-click').forEach(el => {
    el.addEventListener('click', function (event) {
        event.stopPropagation();
    });
});

const select = document.getElementById("unidade");
unidades.forEach((unidade, index) => {
    const option = document.createElement("option");
    option.value = index;
    option.textContent = `${unidade.endereco} - ${unidade.nome}`;
    select.appendChild(option);
});
        </script>
        </div>
@endsection
