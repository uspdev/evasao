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

        $anos = [2015, 2016, 2017, 2018, 2019, 2020];

        $validated = $request->validate([
            'ano' => ['nullable', 'integer', Rule::in($anos)],
        ]);

        $ano = $validated['ano'] ?? '';

        if ($ano) {
            $alunos = Evasao::listarIngressantes($ano);

            foreach ($alunos as &$aluno) {
                $aluno['ano'] = $ano;
                $aluno['curso'] = $aluno['nomcur'] . '/' . $aluno['nomhab'];
                $medias = Evasao::obterMediasAlunoGradGeral($aluno['codpes'], false, $aluno['codpgm']);
                $aluno['status'] = $aluno['data4'] ? 'Encerrado' : 'Ativo';
                $aluno['totalDiscRepr'] = $medias['totalDiscRepr'];
                $aluno['totalDiscAprov'] = $medias['totalDiscAprov'];
                $aluno['mediaPonderadaSuja'] = $medias['mediaPonderadaSuja'];
                $aluno['mediaPonderadaLimpa'] = $medias['mediaPonderadaLimpa'];
                $aluno['beneficio'] = Evasao::obterBeneficiosFormatado($aluno['codpes'], $ano . '-01-01', $aluno['data4']);
            }
        } else {
            $alunos = [];
        }

        return view('evasao.tabela-consolidada', compact('alunos', 'anos', 'ano'));
    }
}
