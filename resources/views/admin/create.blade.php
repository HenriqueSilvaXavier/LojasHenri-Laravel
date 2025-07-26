@extends('layouts.main')

@section('title', 'Criar Produto')

@section('content')
<div id="product-create-container" class="col-md-6 offset-md-3 bg-white p-4 rounded shadow-sm">
  <h1 class="text-center mb-4" style="font-family: 'Eczar', serif; color: #F2A340;">Cadastro de Produto</h1>

  <form action="{{ route('produtos.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="form-group mb-3">
      <label for="imagem">Imagem do Produto:</label>
      <input type="file" id="imagem" name="imagem" class="form-control-file">
    </div>

    <div class="form-group mb-3">
      <label for="nome">Nome:</label>
      <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome do produto">
    </div>

    <div class="form-group mb-3">
      <label for="descricao">Descrição:</label>
      <textarea name="descricao" id="descricao" class="form-control" placeholder="Descreva o produto"></textarea>
    </div>

    <div class="form-group mb-3">
      <label for="preco">Preço:</label>
      <input type="number" step="0.01" class="form-control" id="preco" name="preco" placeholder="Preço do produto">
    </div>

    <div class="form-group mb-3">
      <label for="preco">Promoção:</label>
      <input type="number" class="form-control" id="promocao" name="promocao" placeholder="Promoção do produto">
    </div>
    <div class="form-group mb-3">
      <label for="preco">Fim da promoção:</label>
      <input type="datetime-local" class="form-control" id="fim_promocao" name="fim_promocao" placeholder="Fim da promoção">
    </div>

    <div class="form-group mb-4">
        <label for="categoria">Categoria:</label>
        <select name="categoria" id="categoria" class="form-control">
            @foreach($categorias as $categoria)
            <option value="{{ $categoria }}">{{ $categoria }}</option>
            @endforeach
        </select>
        </div>

        <div class="form-group mb-3">
      <label for="estoque">Estoque:</label>
      <input type="number" class="form-control" id="estoque" name="estoque" placeholder="Estoque do produto">
    </div>
    <div class="text-center">
      <input type="submit" class="btn btn-primary px-5 py-2" value="Criar Produto">
    </div>
  </form>
</div>

@section('scripts')
<script>
  document.getElementById('header').style.flexDirection = 'row';
  document.getElementById('header').style.justifyContent = 'space-between';
  $(document).ready(function() {
    $('#categoria').select2({
      tags: true,
      placeholder: "Escolha ou digite uma categoria",
      allowClear: true
    });
  });
</script>

@endsection
@endsection
