<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Produto;

class AdminController extends Controller{
    public function adminIndex(Request $request){
        \Log::info('Página atual:', ['page' => $request->get('page')]);

        $page = $request->get('page', 1);
        $search = $request->input('search');
        $campo = $request->input('campo', 'nome'); // padrão: nome

        $query = Produto::query();

        if (!is_null($search) && in_array($campo, ['nome', 'preco', 'categoria', 'promocao', 'estoque'])) {
            $query->where($campo, 'like', "%{$search}%");
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

        return redirect('/admin')->with('msg', 'Evento atualizado com sucesso!');
    }
    public function destroy($id){
        Produto::findOrFail($id)->delete();
        return redirect('/admin')->with('msg', 'Evento deletado com sucesso!');
    }
}