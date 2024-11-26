@extends('layouts.app')

@section('content')

@parent

<form class="form-inline" method="POST" action="tabelaConsolidada">
    @csrf

    <div class="form-group mb-2 mx-2">
        <label class="mr-2">Ano inicial:</label>
        <select class="form-control tabela-consolidada" name="ano_inicio">
            <option value="">-- Escolha o ano inicial --</option>
            @foreach($anos as $a)
            <option value="{{ $a }}" {{ old('ano_inicio', $anoInicio) == $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group mb-2 mx-2">
        <label class="mr-2">Ano final:</label>
        <select class="form-control tabela-consolidada" name="ano_fim">
            <option value="">-- Escolha o ano final --</option>
            @foreach($anos as $a)
            <option value="{{ $a }}" {{ old('ano_fim', $anoFim) == $a ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>

    <input class="btn btn-primary mb-2 mx-2" type="submit" name="submit" value="OK">
</form>

@if(isset($alunos) && count($alunos) > 0)
@include('evasao.partials.tabela', ['alunos' => $alunos, 'disciplinasDeInteresse' => $disciplinasDeInteresse])
@else
<p>Nenhum aluno encontrado para o intervalo de anos selecionado.</p>
@endif

@endsection
