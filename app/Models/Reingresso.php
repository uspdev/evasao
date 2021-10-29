<?php

namespace App\Models;

use Uspdev\Replicado\DB;

class Reingresso
{

    /**
     * Retorna lista de alunos que fizeram reingresso no mesmo curso por meio de vestibular
     */
    public static function listarReingresso()
    {

        $query = "SELECT p.codpes, c.nomcur, p.tiping, p.tipencpgm,
        p.dtaing
        from PROGRAMAGR p
        JOIN HABILPROGGR AS h ON (p.codpes = h.codpes AND p.codpgm = h.codpgm)
        JOIN CURSOGR AS c ON (h.codcur = c.codcur)
        JOIN HABILITACAOGR as a ON (h.codhab = a.codhab AND c.codcur = a.codcur)
        WHERE
            -- (p.tipencpgm = 'Encerramento novo ingresso' or p.tipencpgm is NULL) AND -- verifica se ha encerramento por novo ingresso ou está ativo ainda
            c.codclg IN (" . getenv('REPLICADO_CODUNDCLG') . ") AND -- nos cursos da Unidade
            p.dtaing > convert(datetime,'2010-01-01') -- a partir de 2010s
        ORDER BY p.codpes
        ";

        $res = DB::fetchAll($query);

        // dd($res);

        $out = [];
        $prev = $res[0];
        $prev['dtaing'] = substr($prev['dtaing'], 0,10);

        for ($i = 1; $i < count($res); $i++) {
            $row = $res[$i];
            $row['dtaing'] = substr($row['dtaing'], 0,10);
            if (
                $row['codpes'] == $prev['codpes'] && // mesma pessoa,
                $row['dtaing'] != $prev['dtaing'] && //data de ingresso diferente (mecanica tem varios ingressos na mesma data)
                $row['nomcur'] == $prev['nomcur']//mesmo curso
            ) {
                $row['prevtiping'] = $prev['tiping'];
                $row['prevdtaing'] = $prev['dtaing'];
                $row['prevtipencpgm'] = $prev['tipencpgm'];
                $out[] = $row;
            }
            $prev = $row;
        }
        // dd($out);

        return $out;

    }
}
