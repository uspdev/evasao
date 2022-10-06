<?php

namespace App\Replicado;

use Uspdev\Replicado\DB;
use Uspdev\Replicado\Graduacao as GraduacaoReplicado;

class Graduacao extends GraduacaoReplicado
{
    /**
     * Retorna o total de pontos Fuvest de determinada pessoa
     * 
     * total de pontos: codtipmiaing = 130
     *
     * @param int $codpes Identificação da pessoa
     * @param int codpgm Código correspondente ao ingresso
     * @return String Total de pontos com ponto de separador decimal
     * @author Masaki K Neto, 6/10/2022
     */
    public static function obterTotalPontosFuvest(int $codpes, int $codpgm)
    {
        $query = "SELECT ptoing
            FROM NOTASINGRESSOGR
            WHERE codtipmiaing = 130
                AND codpes = :codpes AND codpgm = :codpgm
            ";
        $params = [
            'codpes' => $codpes,
            'codpgm' => $codpgm,
        ];
        $res = DB::fetch($query, $params);
        return $res ? $res['ptoing'] : null;
    }
}
