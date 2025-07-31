<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Produto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\UserInteraction;
use App\Models\Avaliacao;

class ClienteController extends Controller
{
    public function welcome(){
        $userId = Auth::id();

        // Carrossel de promoções
        $carrosselProdutos = Produto::where('promocao', '>', 0)
            ->where('estoque', '>', 0)
            ->where(function ($query) {
                $query->whereNull('fim_promocao')
                    ->orWhere('fim_promocao', '>=', Carbon::now());
            })
            ->orderByDesc('promocao')
            ->take(5)
            ->get();

        // Favoritos e carrinho do usuário
        $favoritos = DB::table('favoritos_users')
            ->where('user_id', $userId)
            ->pluck('produto_id')
            ->toArray();

        $carrinho = DB::table('carrinho_users')
            ->where('user_id', $userId)
            ->pluck('produto_id')
            ->toArray();

        // Produtos em alta nas últimas 24h
        $hoje = Carbon::now()->subHours(24);
        $produtosEmAlta = DB::table('user_interactions')
            ->where('created_at', '>=', $hoje)
            ->select('produto_id', DB::raw('count(*) as total'))
            ->groupBy('produto_id')
            ->orderByDesc('total')
            ->take(9)
            ->pluck('produto_id')
            ->toArray();

        $produtosEmAltaHoje = Produto::whereIn('id', $produtosEmAlta)
            ->withCount('avaliacoes')
            ->withAvg('avaliacoes', 'nota')
            ->get();

        // Executa script Python com userId
        $output = [];
        $escapedUserId = escapeshellarg($userId);
        $basePath = base_path();
        $scriptPath = $basePath . '/python/recomendador.py';
        exec("python3 $scriptPath $escapedUserId 2>&1", $output, $return_var);
        Log::debug("Saída do recomendador: " . print_r($output, true));
        Log::debug("Código de retorno: " . $return_var);

        // Lê os recomendados do arquivo gerado pelo script
        $txtPath = storage_path("app/recomendados_user_{$userId}.txt");
        $recomendados = collect();

        if (file_exists($txtPath)) {
            $ids = file($txtPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!empty($ids)) {
                // Remove produtos em alta da lista recomendada
                $idsFiltrados = array_diff($ids, $produtosEmAlta);

                $recomendados = Produto::whereIn('id', $idsFiltrados)
                    ->withCount('avaliacoes')
                    ->withAvg('avaliacoes', 'nota')
                    ->take(9)
                    ->get();
            }
        }

        // Fallback: preenche com produtos aleatórios caso necessário
        if ($recomendados->isEmpty() || $recomendados->count() < 9) {
            $faltam = 9 - $recomendados->count();
            $idsExistentes = $recomendados->pluck('id')->merge($produtosEmAlta)->all();

            $complementares = Produto::whereNotIn('id', $idsExistentes)
                ->inRandomOrder()
                ->take($faltam)
                ->withCount('avaliacoes')
                ->withAvg('avaliacoes', 'nota')
                ->get();

            $recomendados = $recomendados->merge($complementares);
        }

        // Lista completa de produtos para exibição geral (caso necessário)
        $produtos = Produto::all();

        return view('cliente.welcome', [
            'produtos' => $produtos,
            'recomendados' => $recomendados,
            'carrosselProdutos' => $carrosselProdutos,
            'produtosEmAltaHoje' => $produtosEmAltaHoje,
            'favoritos' => $favoritos,
            'carrinho' => $carrinho
        ]);
    }


    public function toggle(Request $request){
        $userId = auth()->id();
        $produtoId = $request->input('produto_id');

        $exists = DB::table('favoritos_users')
            ->where('user_id', $userId)
            ->where('produto_id', $produtoId)
            ->exists();

        if ($exists) {
            // Remove dos favoritos
            DB::table('favoritos_users')
                ->where('user_id', $userId)
                ->where('produto_id', $produtoId)
                ->delete();

            return response()->json(['status' => 'desfavoritado']);
        } else {
            // Adiciona aos favoritos
            DB::table('favoritos_users')
                ->insert([
                    'user_id' => $userId,
                    'produto_id' => $produtoId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            return response()->json(['status' => 'favoritado']);
        }
    }

    public function categoria(Request $request, $categ)
    {
        $page = $request->get('page', 1);
        $search = $request->input('search');
        $campo = $request->input('campo', 'nome');

        $query = Produto::where('categoria', $categ);

        if ($search) {
            $query->where($campo, 'like', '%' . $search . '%');
        }

        $produtos = $query
            ->withCount('avaliacoes')
            ->withAvg('avaliacoes', 'nota')
            ->paginate(9, ['*'], 'page', $page)
            ->appends(['search' => $search, 'campo' => $campo])
            ->withPath('/categoria/' . $categ);

        $todasCategorias = Produto::pluck('categoria')->filter()->unique();

        $userId = Auth::id();

        $favoritos = DB::table('favoritos_users')
            ->where('user_id', $userId)
            ->pluck('produto_id')
            ->toArray();

        $carrinho = DB::table('carrinho_users')
            ->where('user_id', $userId)
            ->pluck('produto_id')
            ->toArray();

        return view('cliente.categoria', compact('produtos', 'categ', 'todasCategorias', 'favoritos', 'carrinho'));
    }

    public function buscar(Request $request, $busca){
        $page = $request->get('page', 1);
        $produtos = Produto::where('nome', 'like', '%' . $busca . '%')
            ->withCount('avaliacoes')
            ->withAvg('avaliacoes', 'nota')
            ->paginate(9, ['*'], 'page', $page)
            ->withPath('/buscar/' . urlencode($busca));

        $favoritos = DB::table('favoritos_users')
            ->where('user_id', Auth::id())
            ->pluck('produto_id')
            ->toArray();

        $carrinho = DB::table('carrinho_users')
            ->where('user_id', Auth::id())
            ->pluck('produto_id')
            ->toArray();
        return view('cliente.buscar', [
            'produtos' => $produtos,
            'favoritos' => $favoritos,
            'carrinho' => $carrinho,
            'busca' => $busca
        ]);
    }
    public function favoritos(){
        $userId = Auth::id();

        // Pega os IDs dos produtos favoritos do usuário
        $produtosFavoritos = DB::table('favoritos_users')
            ->join('produtos', 'favoritos_users.produto_id', '=', 'produtos.id')
            ->where('favoritos_users.user_id', $userId)
            ->orderByDesc('favoritos_users.created_at')
            ->select('produtos.*') // Pega só os dados dos produtos
            ->get();

        $favoritoIds = $produtosFavoritos->pluck('id')->toArray();

        $produtosCarrinho = DB::table('carrinho_users')
            ->join('produtos', 'carrinho_users.produto_id', '=', 'produtos.id')
            ->where('carrinho_users.user_id', $userId)
            ->orderByDesc('carrinho_users.created_at')
            ->select('produtos.*') // Pega só os dados dos produtos
            ->get();

        $carrinhoIds = $produtosCarrinho->pluck('id')->toArray();

        // Retorna a view 'cliente.favoritos' passando os produtos e IDs favoritos
        return view('cliente.favoritos', [
            'produtos' => $produtosFavoritos,
            'favoritos' => $favoritoIds,
            'carrinho' => $carrinhoIds
        ]);
    }
    public function toggleCarrinho(Request $request)
    {
        $userId = Auth::id();
        $produtoId = $request->input('produto_id');

        $existe = DB::table('carrinho_users')
            ->where('user_id', $userId)
            ->where('produto_id', $produtoId)
            ->exists();

        if ($existe) {
            DB::table('carrinho_users')
                ->where('user_id', $userId)
                ->where('produto_id', $produtoId)
                ->delete();

            return response()->json(['status' => 'removido']);
        } else {
            DB::table('carrinho_users')->insert([
                'user_id' => $userId,
                'produto_id' => $produtoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['status' => 'adicionado']);
        }
    }
    public function carrinho(){
        $userId = Auth::id();

        // Pega os IDs dos produtos favoritos do usuário
        $produtosCarrinho = DB::table('carrinho_users')
            ->join('produtos', 'carrinho_users.produto_id', '=', 'produtos.id')
            ->where('carrinho_users.user_id', $userId)
            ->orderByDesc('carrinho_users.created_at')
            ->select('produtos.*') // Pega só os dados dos produtos
            ->get();

        $carrinhoIds = $produtosCarrinho->pluck('id')->toArray();

        $produtosFavoritos = DB::table('favoritos_users')
            ->join('produtos', 'favoritos_users.produto_id', '=', 'produtos.id')
            ->where('favoritos_users.user_id', $userId)
            ->orderByDesc('favoritos_users.created_at')
            ->select('produtos.*') // Pega só os dados dos produtos
            ->get();

        $favoritosIds = $produtosFavoritos->pluck('id')->toArray();

        // Retorna a view 'cliente.favoritos' passando os produtos e IDs favoritos
        return view('cliente.carrinho', [
            'produtos' => $produtosCarrinho,
            'carrinho' => $carrinhoIds,
            'favoritos' => $favoritosIds
        ]);
    }
    public function finalizarCompra(Request $request) {
        Log::debug('Finalizando compra', ['request' => $request->all()]);

        if (!$request->has('itens') || empty($request->itens)) {
            return response()->json(['error' => 'Nenhum item no carrinho'], 400);
        }

        $user = Auth::user();
        $itens = $request->itens;

        // Agrupa por produto_id somando quantidades (caso tenha múltiplos itens do mesmo produto)
        $agrupados = [];

        foreach ($itens as $item) {
            if (!isset($item['produto_id'], $item['quantidade'])) continue;

            $produtoId = (string) $item['produto_id'];
            $quantidade = (int) $item['quantidade'];

            if (!isset($agrupados[$produtoId])) {
                $agrupados[$produtoId] = 0;
            }

            $agrupados[$produtoId] += $quantidade;
        }

        // Cria os registros no banco
        foreach ($agrupados as $produtoId => $quantidade) {
            UserInteraction::create([
                'user_id' => $user->id,
                'produto_id' => $produtoId,
                'tipo' => 'comprou',
                'quantidade' => $quantidade, // Usa a quantidade enviada
                'cpf' => $request->cpf,
                'formaReceber' => $request->formaReceber,
                'endereco' => $request->endereco,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'tipoEntrega' => $request->tipoEntrega,
                'unidadeEscolhida' => $request->unidadeEscolhida ?? null,
                'cartao' => $request->cartao ?? null,
                'parcelamento' => $request->parcelamento ?? null,
                'numeroCartao' => $request->numeroCartao ?? null
            ]);

            // Atualiza estoque
            $produto = Produto::find($produtoId);
            if ($produto && $produto->estoque >= $quantidade) {
                $produto->estoque -= $quantidade;
                $produto->save();
            } else {
                Log::warning("Produto $produtoId sem estoque suficiente.");
            }
        }

        return response()->json(['status' => 'ok', 'message' => 'Compra registrada com sucesso']);
    }

    public function produto(Request $request, $id){
        $produto = Produto::findOrFail($id);

        $favoritos = DB::table('favoritos_users')
            ->where('user_id', Auth::id())
            ->pluck('produto_id')
            ->toArray();

        $carrinho = DB::table('carrinho_users')
            ->where('user_id', Auth::id())
            ->pluck('produto_id')
            ->toArray();

        $page = $request->get('page', 1);
        // Buscar avaliações com usuário
        $avaliacoes = Avaliacao::with('user')
            ->where('produto_id', $id)
            ->orderBy('data_avaliacao', 'desc')
            ->paginate(8, ['*'], 'page', $page)
            ->withPath('/produto/' . $id);

        // Calcular média
        $mediaNotas = round($avaliacoes->avg('nota'), 1);
        $minhaAvaliacao = Avaliacao::where('user_id', Auth::id())
            ->where('produto_id', $id)
            ->first();

        $relacionados = Produto::where('categoria', $produto->categoria)
            ->where('id', '!=', $id)
            ->withCount('avaliacoes')
            ->withAvg('avaliacoes', 'nota')
            ->take(4)
            ->get();
        
        return view('cliente.produto', compact('produto', 'favoritos', 'carrinho', 'avaliacoes', 'mediaNotas', 'minhaAvaliacao', 'relacionados'));
    }
    public function avaliar(Request $request, $id){
        $request->validate([
            'suaNota' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|max:1000',
        ]);
        if (Avaliacao::where('user_id', Auth::id())
            ->where('produto_id', $id)
            ->exists()) {
            $avaliacao = Avaliacao::where('user_id', Auth::id())
                ->where('produto_id', $id)
                ->first();

            $avaliacao->nota = $request->suaNota;
            $avaliacao->comentario = $request->comentario;
            $avaliacao->data_avaliacao = now();
            $avaliacao->save();

            return redirect()->back()->with('success', 'Avaliação atualizada com sucesso!');
        }
        $avaliacao = new Avaliacao();
        $avaliacao->user_id = Auth::id();
        $avaliacao->produto_id = $id;
        $avaliacao->nota = $request->suaNota; // Use suaNota para
        $avaliacao->comentario = $request->comentario;
        $avaliacao->data_avaliacao = now();
        $avaliacao->save();

        return redirect()->back()->with('success', 'Avaliação enviada com sucesso!');
    }

    public function registrarVisualizacao(Request $request) {

        $user = Auth::user();
        $itens = $request->itens;

        // Cria os registros no banco
        foreach ($request->itens as $item) {
            UserInteraction::create([
                'user_id' => $user->id,
                'produto_id' => $item['produtoId'],
                'tipo' => 'visualizou',
            ]);
        }

        return response()->json(['status' => 'ok', 'message' => 'Visualização registrada com sucesso']);
    }

}
