<?php

namespace App\Http\Controllers;

use App\Models\Evasao;
use App\Models\Reingresso;
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

        $anos = range(2010, date('Y'));

        if ($request->isMethod('get')) {

            $anoInicio = 2010;
            $anoFim = date('Y');  // Ano final padrão (ano atual)
            return view('evasao.tabela-consolidada', compact('anos', 'anoInicio', 'anoFim'));
        }

        $validated = $request->validate([
            'ano_inicio' => ['nullable', 'numeric', 'integer', Rule::in($anos)],
            'ano_fim' => ['nullable', 'numeric', 'integer', Rule::in($anos)],
        ]);

        $anoInicio = intval($validated['ano_inicio'] ?? 2010);
        $anoFim = intval($validated['ano_fim'] ?? date('Y'));

        if ($anoInicio > $anoFim) {
            return back()->withErrors(['intervalo' => 'O ano inicial deve ser menor ou igual ao ano final.']);
        }

        $alunos = [];
        foreach (range($anoInicio, $anoFim) as $ano) {
            $alunos = array_merge($alunos, Evasao::consolidarPorAno($ano));
        }

        $disciplinasDeInteresse = Evasao::disciplinasDeInteresse();

        return view('evasao.tabela-consolidada', compact('alunos', 'anos', 'anoInicio', 'anoFim', 'disciplinasDeInteresse'));
    }

    public function reingresso()
    {
        $this->authorize('admin');
        $reingresso = Reingresso::listarReingresso();
        return view('reingresso', compact('reingresso'));
    }
}
