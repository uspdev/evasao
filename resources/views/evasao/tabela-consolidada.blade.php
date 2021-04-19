@extends('master')

@section('content')
@parent
<form class="form-inline" method="POST" action="tabelaConsolidada">
    @csrf
    <div class="form-group mb-2 mx-2">
        <Label class="mr-2">Tabela consolidada por ano de ingresso</Label>
        <select class="form-control tabela-consolidada" name="ano">
            <option value=''>-- Escolha um ano --</option>
            @foreach($anos as $a)
            <option value="{{ $a }}" {{ $ano == $a ? 'selected':'' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>
    <input class="btn btn-primary mb-2 mx-2" type="submit" name="submit" value="OK">
</form>

@includewhen($alunos, 'evasao.partials.tabela')
@endsection
