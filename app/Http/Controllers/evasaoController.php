<?php

namespace App\Http\Controllers;

use App\Models\Evasao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Uspdev\Replicado\Graduacao;

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

        $anos = range(2010, 2021);

        // para estas disiplinas, serão apresentadas o nro de reprovações do aluno
        $disciplinasDeInteresse = ['SMA0353', 'SMA0354', 'SMA0355', 'SMA0356', 'SMA0300', 'SMA0304', 'SME0320', '7600005', '7500012'];

        $validated = $request->validate([
            'ano' => ['nullable', 'integer', Rule::in($anos)],
        ]);

        $ano = $validated['ano'] ?? '';

        if ($ano) {
            $alunos = Evasao::listarIngressantes($ano);

            foreach ($alunos as &$aluno) {
                $aluno['ano'] = $ano;
                $aluno['curso'] = $aluno['nomcur'] . '/' . $aluno['nomhab'];
                $aluno['status'] = $aluno['data4'] ? 'Encerrado' : 'Ativo';
                // medias, disciplinas aprovadas e reprovadas
                $aluno = array_merge($aluno, Evasao::obterMediasAlunoGradGeral($aluno['codpes'], false, $aluno['codpgm']));

                // disciplinas de interesse
                foreach ($disciplinasDeInteresse as $d) {
                    $di["di_$d"] = 0;
                }
                $ds = Graduacao::listarDisciplinasAluno($aluno['codpes'], $aluno['codpgm']);
                foreach ($ds as $d) {
                    if (in_array($d['coddis'], $disciplinasDeInteresse)) { // se for uma disciplina de interesse
                        if (in_array($d['rstfim'], ['RN', 'RA', 'RF'])) { // se houver reprovação
                            $di['di_' . $d['coddis']] = $di['di_' . $d['coddis']] + 1;
                        }
                    }
                }
                $aluno = array_merge($aluno, $di);

                $aluno['beneficio'] = Evasao::obterBeneficiosFormatado($aluno['codpes'], $ano . '-01-01', $aluno['data4']);
            }
        } else {
            $alunos = [];
        }

        return view('evasao.tabela-consolidada', compact('alunos', 'anos', 'ano', 'disciplinasDeInteresse'));
    }
}
