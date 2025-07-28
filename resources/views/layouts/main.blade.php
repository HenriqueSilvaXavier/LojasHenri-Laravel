<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="/img/favicon.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Rajdhani:wght@400;600;700&family=Share+Tech+Mono&display=swap" rel="stylesheet">
</head>
<body>
    <header id="header">
        @php
            $routeAction = request()->route()->getActionName();
            $isClienteController = str_contains($routeAction, 'ClienteController');
            $isAdminController = str_contains($routeAction, 'AdminController');
        @endphp
        <a href="{{ route('cliente.welcome') }}">
            <h1>Lojas Henri</h1>
        </a>
        @auth
            @if($isClienteController)
                <input type="search" name="procurar" id="procurar">
            @endif
            <div class="icones-usuario relative flex items-center gap-3">
                @if($isClienteController)
                    <div class="icon-container">
                        <a href="{{ route('cliente.carrinho') }}">
                            <img src="/img/carrinho.png" alt="Carrinho" id="carrinho" class="w-8 h-8">
                            @if(count($carrinho) > 0 && count($carrinho) < 10)
                                <span class="contador" id="contador-carrinho">{{ count($carrinho) }}</span>
                            @elseif(count($carrinho) >= 10)
                                <span class="contador" id="contador-carrinho2">9+</span>
                            @endif
                        </a>
                    </div>
                    <div class="icon-container">
                        <a href="{{ route('cliente.favoritos') }}">
                            <img src="/img/heart.png" alt="Favoritos" id="coracao" class="w-8 h-8">
                        </a>
                    </div>
                @endif
                    <abbr title="Perfil" style="cursor: pointer" onclick="abrirModalPerfil()">
                        <img src="/img/foto.jpg" alt="Perfil" id="perfil" style="cursor: pointer;">
                    </abbr>
            </div>


            <!-- Modal com formulário de logout -->
            <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="profileModalLabel">Informações do Perfil</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nome de Usuário:</label>
                                <p>{{ Auth::user()->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email:</label>
                                <p>{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        @if($isClienteController && Auth::user()->isAdmin() == true)
                            <div class="modal-footer">
                                <a href="{{ route('admin') }}" class="btn btn-primary">
                                    <i class="fas fa-tachometer-alt me-1"></i> Ir para o Admin
                                </a>
                            </div>
                        @elseif($isAdminController)
                            <div class="modal-footer">
                                <a href="{{ route('cliente.welcome') }}" class="btn btn-primary">
                                    <i class="fas fa-home me-1"></i> Voltar para o Cliente
                                </a>
                            </div>
                        @endif
                        <div class="modal-footer">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-sign-out-alt me-1"></i> Sair
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </header>

    @if($isClienteController)
        <nav class="relative w-full">
            <div id="scroll-categorias-container" class="flex items-center relative">
                <button id="seta-esquerda" class="seta-scroll hidden">&#8592;</button>
                <div id="scroll-categorias" class="flex gap-4 overflow-hidden">
                    @foreach ($todasCategorias as $categoria)
                        <a href="{{ route('cliente.categoria', ['categ' => $categoria]) }}"
                           class="categoria whitespace-nowrap inline-block py-2 px-4 bg-gray-200 rounded-lg cursor-pointer hover:bg-gray-300">
                            {{ $categoria }}
                        </a>
                    @endforeach
                </div>
                <button id="seta-direita" class="seta-scroll hidden">&#8594;</button>
            </div>
        </nav>
    @endif

    @yield('content')

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>Sobre</h4>
                <p>Somos a Lojas Henri, comprometida com os melhores preços e recomendações personalizado.</p>
            </div>
            <div class="footer-section">
                <h4>Links úteis</h4>
                <ul>
                    <li><a href="{{ route('cliente.welcome') }}">Início</a></li>
                    <li><a href="{{ route('cliente.favoritos') }}">Favoritos</a></li>
                    <li><a href="{{ route('cliente.carrinho') }}">Carrinho</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contato</h4>
                <p>Email: henriquesx44@gmail.com</p>
                <p>WhatsApp: (81) 8552-1110</p>
            </div>
            <div class="footer-section">
                <h4>Redes Sociais</h4>
                <div class="social-icons">
                    <a href="https://www.instagram.com/henriquesilvaxavier8/" target="_blank"><img src="/img/instagram.png" alt="Instagram"></a>
                    <a href="https://www.youtube.com/@henriquesilva6249/" target="_blank"><img src="/img/youtube.png" alt="YouTube"></a>
                    <a href="https://www.linkedin.com/in/henrique-silva-xavier-3a4047241/" target="_blank"><img src="/img/linkedin.png" alt="LinkedIn"></a>
                </div>
            </div>
        </div>
        <div class="copyright">
            &copy; <span id="anoAtual"></span> Henrique Silva Xavier. Todos os direitos reservados.
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <div id="custom-backdrop" class="custom-backdrop"></div>
    <script>
        const container = document.getElementById('scroll-categorias');
        const categorias = Array.from(container.querySelectorAll('.categoria'));
        const setaDireita = document.getElementById('seta-direita');
        const setaEsquerda = document.getElementById('seta-esquerda');

        let paginas = [];
        let paginaAtual = 0;

        function calcularPaginas() {
            paginas = [];
            let pagina = [];
            let larguraContainerPai = document.getElementById('scroll-categorias-container').offsetWidth;
            let larguraUsada = 0;

            // Mostra todos para medir corretamente
            categorias.forEach(cat => {
                cat.style.display = 'inline-block';
            });

            categorias.forEach((categoria) => {
                const largura = categoria.offsetWidth + 16; // 16 = gap
                if (larguraUsada + largura > larguraContainerPai && pagina.length > 0) {
                    paginas.push(pagina);
                    pagina = [];
                    larguraUsada = 0;
                }
                pagina.push(categoria);
                larguraUsada += largura;
            });

            if (pagina.length > 0) {
                paginas.push(pagina);
            }
        }

        function exibirPagina(index) {
            paginaAtual = index;
            categorias.forEach(cat => cat.style.display = 'none');
            if (paginas[paginaAtual]) {
                paginas[paginaAtual].forEach(cat => cat.style.display = 'inline-block');
            }
            setaEsquerda.style.display = paginaAtual > 0 ? 'block' : 'none';
            setaDireita.style.display = paginaAtual < paginas.length - 1 ? 'block' : 'none';
        }

        setaDireita.addEventListener('click', () => {
            if (paginaAtual < paginas.length - 1) {
                exibirPagina(paginaAtual + 1);
            }
        });

        setaEsquerda.addEventListener('click', () => {
            if (paginaAtual > 0) {
                exibirPagina(paginaAtual - 1);
            }
        });

        function inicializarCategoriasPaginadas() {
            // Mostra todos antes de recalcular
            categorias.forEach(cat => {
                cat.style.display = 'inline-block';
            });

            calcularPaginas();
            exibirPagina(0);
        }


        window.addEventListener('resize', inicializarCategoriasPaginadas);
        window.addEventListener('load', inicializarCategoriasPaginadas);

        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('procurar');
            if (input) {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        const valor = input.value.trim();
                        if (valor !== '') {
                            window.location.href = `/buscar/${encodeURIComponent(valor)}`;
                        }
                    }
                });
            }
        });

function abrirModalPerfil() {
    const backdrop = document.getElementById('custom-backdrop');
    backdrop.style.display = 'block';

    const modal = new bootstrap.Modal(document.getElementById('profileModal'), {
        backdrop: false, // desativa o backdrop padrão do Bootstrap
        keyboard: true
    });
    modal.show();

    // Quando o modal for fechado, esconda o backdrop também
    const modalElement = document.getElementById('profileModal');
    modalElement.addEventListener('hidden.bs.modal', () => {
        backdrop.style.display = 'none';
    }, { once: true }); // garante que só escute uma vez
}

function atualizarContadorCarrinho(novoValor) {
    const contadores = document.querySelectorAll('.contador');
    contadores.forEach(contador => {
        if (novoValor >= 10) {
            contador.textContent = '9+';
        } else {
            contador.textContent = novoValor;
        }
    });
}


    </script>

    @yield('scripts')
</body>
</html>
