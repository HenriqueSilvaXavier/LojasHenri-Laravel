<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Produto;

class AdminController extends Controller{
    public function adminIndex(Request $request){
        $page = $request->get('page', 1);
        $search = $request->input('search');
        $campo = $request->input('campo', 'nome'); // padrão: nome

        $query = Produto::query();

        if (!is_null($search) && in_array($campo, ['nome', 'preco', 'categoria', 'promocao', 'estoque', 'fim_promocao'])) {
            if ($campo === "fim_promocao") {
                // Tenta múltiplos formatos de data
                $formatos = ['d/m/Y H:i', 'd/m/Y', 'd/m']; // d/m é mais permissivo, cuidado
                $dataConvertida = null;

                foreach ($formatos as $formato) {
                    try {
                        $dataConvertida = \Carbon\Carbon::createFromFormat($formato, $search);
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                if ($dataConvertida) {
                    $query->whereNotNull('fim_promocao');

                    // Se o usuário digitou apenas a data (sem hora), buscamos por toda a data
                    if (strlen($search) <= 10) {
                        $query->whereDate('fim_promocao', '=', $dataConvertida->format('Y-m-d'));
                    } else {
                        $query->where('fim_promocao', '=', $dataConvertida->format('Y-m-d H:i:s'));
                    }
                } else {
                    \Log::warning('Formato inválido de fim_promocao: ' . $search);
                }
            } else {
                $query->where($campo, 'like', "%{$search}%");
            }
        }

        $produtos = $query->paginate(100, ['*'], 'page', $page)
            ->appends(['search' => $search, 'campo' => $campo])
            ->withPath('/admin');

        return view('admin.index', compact('produtos'));
    }


    public function create(){
        $categorias = Produto::select('categoria')->distinct()->pluck('categoria');
        return view('admin.create', compact('categorias'));
    }
    public function store(Request $request)
    {
        $produto = new Produto();
        $produto->nome = $request->nome;
        $produto->descricao = $request->descricao;
        $produto->preco = $request->preco;
        $produto->promocao = $request->promocao;
        $produto->fim_promocao = $request->fim_promocao;
        $produto->categoria = $request->categoria;
        $produto->estoque = $request->estoque;
        if($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
            $requestImage = $request->imagem;
            $extension = $requestImage->extension();
            $imageName = md5($requestImage->getClientOriginalName() . strtotime('now')) . '.' . $extension;
            $request->imagem->move(public_path('img/produtos'), $imageName);
            $produto->imagem = $imageName;
        } 
        $produto->save();

        return redirect('/admin')->with('msg', 'Evento criado com sucesso!');
    }
    public function edit($id){
        $categorias = Produto::select('categoria')->distinct()->pluck('categoria');
        $produto = Produto::findOrFail($id);

        return view('admin.edit', compact('produto', 'categorias'));
    }
    public function update(Request $request, $id){
        $produto = Produto::find($id);
        $produto->nome = $request->nome;
        $produto->descricao = $request->descricao;
        $produto->preco = $request->preco;
        $produto->promocao = $request->promocao;
        $produto->fim_promocao = $request->fim_promocao;
        $produto->categoria = $request->categoria;
        $produto->estoque = $request->estoque;
        if ($request->filled('fim_promocao')) {
            $produto->fim_promocao = $request->fim_promocao;
        } else {
            $produto->fim_promocao = null;
        }
        if($request->hasFile('imagem') && $request->file('imagem')->isValid()) {
            $requestImage = $request->imagem;
            $extension = $requestImage->extension();
            $imageName = md5($requestImage->getClientOriginalName() . strtotime('now')) . '.' . $extension;
            $request->imagem->move(public_path('img/produtos'), $imageName);
            $produto->imagem = $imageName;
        } 
        $produto->save();

        return redirect('/admin')->with('msg', 'Evento atualizado com sucesso!');
    }
    public function destroy($id){
        Produto::findOrFail($id)->delete();
        return redirect('/admin')->with('msg', 'Evento deletado com sucesso!');
    }
}
