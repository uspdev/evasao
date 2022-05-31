<?php

namespace App\Http\Controllers;

use App\Models\Evasao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class evasaoController extends Controller
{

    public function index()
    {
        if (!Gate::allows('admin')) {
            return view('index');
        }

        return view('home');
    }

    public function tabelaConsolidada(Request $request)
    {
        $this->authorize('admin');

        $anos = array_merge(range(2015, 2022), ['todos']);

        $validated = $request->validate([
            'ano' => ['nullable', 'integer', Rule::in($anos)],
        ]);

        $ano = $validated['ano'] ?? '';
        $alunos = [];

        if ($ano) {
            if ($ano == 'todos') {
                foreach ($anos as $ano) {
                    $alunosPorAno = Evasao::consolidarPorAno($ano);
                    $alunos = array_merge($alunos, $alunos);
                }
            } else {
                $alunos = Evasao::consolidarPorAno($ano);
            }
        }

        $disciplinasDeInteresse = Evasao::disciplinasDeInteresse();

        return view('evasao.tabela-consolidada', compact('alunos', 'anos', 'ano', 'disciplinasDeInteresse'));
    }
}
