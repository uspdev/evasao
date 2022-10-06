<?php

namespace App\Models;

use \Uspdev\Replicado\DB;
use Uspdev\Replicado\Pessoa;
use App\Replicado\Graduacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evasao extends Model
{
    use HasFactory;

    public static function disciplinasDeInteresse()
    {
        // para estas disiplinas, serão apresentadas o nro de reprovações do aluno
        $disciplinasDeInteresse = ['SMA0353', 'SMA0354', 'SMA0355', 'SMA0356', 'SMA0300', 'SMA0304', 'SME0320', '7600005', '7500012'];
        return $disciplinasDeInteresse;
    }

    public static function consolidarPorAno($ano)
    {
        $alunos = Evasao::listarIngressantes($ano);
        foreach ($alunos as &$aluno) {
            $aluno['ano'] = $ano;
            $aluno['status'] = $aluno['data4'] ? 'Encerrado' : 'Ativo';
            // medias, disciplinas aprovadas e reprovadas
            $aluno = array_merge($aluno, Evasao::obterMediasAlunoGradGeral($aluno['codpes'], false, $aluno['codpgm']));

            // disciplinas de interesse
            foreach (SELF::disciplinasDeInteresse() as $d) {
                $di["di_$d"] = 0;
            }
            $ds = Graduacao::listarDisciplinasAluno($aluno['codpes'], $aluno['codpgm']);
            foreach ($ds as $d) {
                if (in_array($d['coddis'], SELF::disciplinasDeInteresse())) { // se for uma disciplina de interesse
                    if (in_array($d['rstfim'], ['RN', 'RA', 'RF'])) { // se houver reprovação
                        $di['di_' . $d['coddis']] = $di['di_' . $d['coddis']] + 1;
                    }
                }
            }
            $aluno = array_merge($aluno, $di);

            $aluno['beneficio'] = Evasao::obterBeneficiosFormatado($aluno['codpes'], $ano . '-01-01', $aluno['data4']);

            // pais de Nascimento
            if (in_array($aluno['tipdocidf'], ['RNE', 'ProRNE', 'Passap', 'RNM', 'CIEE', 'DIE'])) {
                $aluno['origem'] = Pessoa::obterComplemento($aluno['codpes'])['nompas'];
            } else {
                $aluno['origem'] = $aluno['sglest'];
            }

            // Total de pontos para ingressante fuvest
            $aluno['ptoing'] = Graduacao::obterTotalPontosFuvest($aluno['codpes'], $aluno['codpgm']);
        }
        return $alunos;
    }

    public static function listarIngressantes(int $ano)
    {
        $codundclg = getenv('REPLICADO_CODUNDCLG');
        #-- query para gerar a lista de alunos a serem processadas.
        #-- a quantidade retornada será a quantidade de linhas da planilha final
        $query = "SELECT p.codpes, ps.sexpes, YEAR(ps.dtanas) anonas, ps.tipdocidf, ps.sglest,
            p.codpgm, p.tiping, p.tipencpgm, p.sglacaafm,
            CONVERT(VARCHAR(10),p.dtaing ,103) AS data1, p.clsing, p.stapgm,
            CONVERT(VARCHAR(10),p.dtaini ,103) AS data2, p.tipencpgm,
            h.codcur, h.codhab, CONVERT(VARCHAR(10),h.dtaini ,103) AS data3, h.clsdtbalutur,
            CONVERT(VARCHAR(10),h.dtafim ,103) AS data4, h.tipenchab,
            c.nomcur, a.nomhab
            FROM PROGRAMAGR AS p
            JOIN HABILPROGGR AS h ON (p.codpes = h.codpes AND p.codpgm = h.codpgm)
            JOIN CURSOGR AS c ON (h.codcur = c.codcur)
            JOIN HABILITACAOGR as a ON (h.codhab = a.codhab AND c.codcur = a.codcur)
            JOIN PESSOA ps ON (p.codpes = ps.codpes)
            WHERE
            c.codclg IN ($codundclg) AND
            p.dtaing >= '{$ano}-01-01' AND p.dtaing <= '{$ano}-12-31' -- ingresso no ano
            ORDER BY
            p.codpes, h.codcur, a.codhab;
        ";

        return DB::fetchAll($query);
    }

    public static function obterBeneficiosFormatado($codpes, $dataini, $datafim = null)
    {
        if ($beneficios = Evasao::listarBeneficios($codpes, $dataini, $datafim)) {
            $ret = '';
            foreach ($beneficios as $b) {
                $ret .= "{$b['nome']} ({$b['data_inicio']}-{$b['data_fim']}), ";
            }
            $ret = substr($ret, 0, -2);
        } else {
            $ret = 'não';
        }
        return $ret;
    }

    public static function listarBeneficios($codpes, $dataini, $datafim = null)
    {
        if ($datafim) {
            $datafim_query = 'AND a.dtafimccdori <= :datafim';
            $param['datafim'] = $datafim;
        } else {
            $datafim_query = '';
        }

        $query = "SELECT b.codbnfalu AS cod, b.tipbnfalu AS tipo, b.nombnfloc AS nome,
                CONVERT(VARCHAR(10),a.dtainiccd ,103) AS data_inicio,
                CONVERT(VARCHAR(10), a.dtafimccdori ,103) AS data_fim, a.vlrbnfepfbls AS valor
            FROM BENEFICIOALUCONCEDIDO a
            JOIN BENEFICIOALUNO b ON (a.codbnfalu = b.codbnfalu)
            WHERE a.codpes=:codpes
                AND a.dtainiccd >= :dataini -- daita de inicio do benefício
                $datafim_query -- data fim do beneficio
            ORDER BY a.dtafimccd DESC";

        $param['codpes'] = $codpes;
        $param['dataini'] = $dataini;

        return DB::fetchAll($query, $param);
    }

    /**
     * Método que retorna as médias de um aluno específico
     * 
     * Conforme Graduacao::obterMediasAlunoGrad
     *
     * @param $nusp nusp do aluno validado
     * @param $porSemestre: boolean, agrupar por semestre, default false
     * @param $entrada: referente a matrícula
     * @return Array medias
     */
    public static function obterMediasAlunoGradGeral($codpes, $porSemestre = false, $codpgm = 1)
    {
        $entrada = $codpgm;
        $sql = "SELECT  SUBSTRING(h.codtur, 1, 5) AS semestre, h.codtur, h.coddis, d.nomdis,
                    h.codpes, h.stamtr, h.rstfim , d.creaul, d.cretrb, h.notfim AS nota1, h.notfim2 AS nota2
                FROM HISTESCOLARGR AS h, TURMAGR AS t, DISCIPLINAGR AS d, HABILPROGGR AS g
                WHERE t.coddis = d.coddis AND h.codpes = :codpes AND h.codtur = t.codtur AND h.coddis = t.coddis
                    AND h.verdis = t.verdis AND h.verdis = d.verdis  AND h.rstfim IS NOT NULL
                    AND g.codpes = h.codpes AND g.codpgm = h.codpgm  AND g.codpgm = :codpgm
                    AND h.rstfim NOT IN ('D', 'T')
                    AND h.stamtr='M'
                ORDER BY h.codtur, h.coddis ; ";

        $sql = "SELECT SUBSTRING(h.codtur, 1, 5) AS semestre, h.codtur, h.coddis, d.nomdis,
                    h.codpes, h.stamtr, h.rstfim , d.creaul, d.cretrb, h.notfim AS nota1, h.notfim2 AS nota2,
                    d.nomdis, d.creaul, d.cretrb
                    --h.*, d.*
                FROM HISTESCOLARGR h
                JOIN DISCIPLINAGR d ON (h.verdis = d.verdis AND h.coddis = d.coddis)
                WHERE h.stamtr='M' --efetivamente matriculado
                    AND h.rstfim not in ('D', 'T') AND h.rstfim IS NOT NULL --resultado final: D-equivalencia, T-trancamento
                    AND h.codpgm = :codpgm -- codigo-programa = numero do ingresso
                    AND h.codpes=:codpes";

        $param['codpes'] = $codpes;
        $param['codpgm'] = $codpgm;

        $disciplinas = DB::fetchAll($sql, $param);

        // print_r($disciplinas);exit;
        $ret = array();
        $mediaSuja = 0;
        $somaNotaSuja = 0;
        $mediaLimpa = 0;
        $somaNotaLimpa = 0;
        $qtdTotal = 0;
        $qtdAprovada = 0;
        $ponderacao = 0;

        $totalDiscAprov = 0;
        $totalDiscRepr = 0;

        $semestre = array();
        $s = [
            'disciplinas' => 0,
            'mediaSuja' => 0,
            'somaNotaSuja' => 0,
            'mediaLimpa' => 0,
            'somaNotaLimpa' => 0,
            'qtdTotal' => 0,
            'qtdAprovada' => 0,
            'ponderacao' => 0,
        ];
        $semestreAtual = 'BLA';

        foreach ($disciplinas as $row) {
            $row = (object) $row;
            $ponderacaoDisciplina = 0;

            //verificando se mudou de semestre
            if ($semestreAtual != $row->semestre) {
                //calcular os valores finais deste semestre
                if ($s['qtdAprovada'] > 0) {
                    $s['mediaLimpa'] = $s['somaNotaLimpa'] / $s['qtdAprovada'];
                }

                if ($s['qtdTotal'] > 0) {
                    $s['mediaSuja'] = ($s['somaNotaSuja'] + $s['somaNotaLimpa']) / $s['qtdTotal'];
                }

                //guardar o semestre no acumulador
                $semestre[$semestreAtual] = $s;

                // vamos começar todo de novo
                $semestreAtual = $row->semestre;
                $s = [
                    'disciplinas' => 0,
                    'mediaSuja' => 0,
                    'somaNotaSuja' => 0,
                    'mediaLimpa' => 0,
                    'somaNotaLimpa' => 0,
                    'qtdTotal' => 0,
                    'qtdAprovada' => 0,
                    'ponderacao' => 0,
                ];
            }

            // processando as medias para um semestre
            $s['disciplinas'] += 1;
            $notaPonderadaDisciplina = 0;
            $ponderacaoDisciplina = ($row->creaul + $row->cretrb);

            if ($row->nota2 && ($row->nota2 > $row->nota1)) { //teve recuperação
                $notaPonderadaDisciplina = ($row->nota2) * $ponderacaoDisciplina;
            } else { // nota normal
                $notaPonderadaDisciplina = ($row->nota1) * $ponderacaoDisciplina;
            }

            if (trim($row->rstfim) == 'A') { // se foi aprovado/reprovado
                $s['somaNotaLimpa'] += $notaPonderadaDisciplina;
                $s['qtdAprovada'] += $ponderacaoDisciplina;
            } else {
                $s['somaNotaSuja'] += $notaPonderadaDisciplina;
            }

            $s['qtdTotal'] += $ponderacaoDisciplina;

            // processando as medias gerais
            $notaPonderada = 0;
            $ponderacao = ($row->creaul + $row->cretrb);
            if ($row->nota2 && ($row->nota2 > $row->nota1)) { //teve recuperação
                $notaPonderada = ($row->nota2) * $ponderacao;
            } else { // nota normal
                $notaPonderada = ($row->nota1) * $ponderacao;
            }

            if (trim($row->rstfim) == 'A') {
                $somaNotaLimpa += $notaPonderada;
                $qtdAprovada += $ponderacao;
                $totalDiscAprov++;
            } else {
                $somaNotaSuja += $notaPonderada;
            }

            if (trim($row->rstfim) != 'A') {
                $totalDiscRepr++;
            }

            $qtdTotal += $ponderacao;
        }

        //ÚLTIMO SEMESTRE...
        //calcular os valores finais deste semestre
        if ($s['qtdAprovada'] > 0) {
            $s['mediaLimpa'] = $s['somaNotaLimpa'] / $s['qtdAprovada'];
        }

        if ($s['qtdTotal'] > 0) {
            $s['mediaSuja'] = ($s['somaNotaSuja'] + $s['somaNotaLimpa']) / $s['qtdTotal'];
        }

        //guardar o semestre no acumulador
        $semestre[$semestreAtual] = $s;

        if ($qtdAprovada > 0) {
            $mediaLimpa = $somaNotaLimpa / $qtdAprovada;
        }

        if ($qtdTotal > 0) {
            $mediaSuja = ($somaNotaSuja + $somaNotaLimpa) / $qtdTotal;
        }
        //print_r($semestre);exit;

        if ($porSemestre) {
            unset($semestre['BLA']);
            $ret = array('mediaPonderadaLimpa' => sprintf("%01.1f", $mediaLimpa), 'mediaPonderadaSuja' => sprintf("%01.1f", $mediaSuja), 'semestres' => $semestre);
        } else {
            $ret = array('mediaPonderadaLimpa' => sprintf("%01.1f", $mediaLimpa), 'mediaPonderadaSuja' => sprintf("%01.1f", $mediaSuja));
        }

        // numero de disciplinas
        $ret['entrada'] = $entrada;
        $ret['totalDiscRepr'] = $totalDiscRepr;
        $ret['totalDiscAprov'] = $totalDiscAprov;
        $ret['qtdTotal'] = count($disciplinas);

        //print_r($ret);exit;
        return $ret;
    }

    // nao vai precisar pois vai usar do datatables
    public static function toCsv($colecao, $filename)
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0",
        );

        $callback = function () use ($colecao) {
            $output = fopen("php://output", 'w') or die("Can't open php://output");
            foreach ($colecao as $row) {

                fputcsv($output, [
                    $row['ano'],
                    $row['curso'],
                    $row['tiping'],
                    $row['codpes'],
                    $row['status'],
                    $row['tipenchab'],
                    $row['beneficio'],
                    $row['totalDiscRepr'],
                    $row['totalDiscAprov'],
                    $row['mediaPonderadaSuja'],
                    $row['mediaPonderadaLimpa'],
                ]);
            }
            fclose($output) or die("Can't close php://output");
        };

        return response()->stream($callback, 200, $headers);
    }
}
