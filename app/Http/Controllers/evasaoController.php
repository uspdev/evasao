<?php

namespace App\Http\Controllers;

use App\Models\Evasao;
use App\Models\Reingresso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

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
            'ano' => ['nullable', Rule::in($anos)],
        ]);

        $ano = $validated['ano'] ?? null;
        $alunos = [];

        if ($ano) {
            if ($ano == 'todos') {
                $anosLoop = $anos;
                array_pop($anosLoop);
                foreach ($anosLoop as $ano) {
                    $alunosPorAno = Evasao::consolidarPorAno($ano);
                    $alunos = array_merge($alunos, $alunosPorAno);
                }
            } else {
                $alunos = Evasao::consolidarPorAno($ano);
            }
        }

        $disciplinasDeInteresse = Evasao::disciplinasDeInteresse();

        return view('evasao.tabela-consolidada', compact('alunos', 'anos', 'ano', 'disciplinasDeInteresse'));
    }

    // Garante que o usuário é autenticado e possui o nível 'admin' para acessar o reingresso
    public function reingresso()
    {
        $this->authorize('admin');

        $reingresso = Reingresso::listarReingresso();

        return view('reingresso', compact('reingresso'));
    }
}
