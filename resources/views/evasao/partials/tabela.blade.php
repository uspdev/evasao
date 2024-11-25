<table class="table table-bordered table-hover table-sm alunos datatable-simples dt-buttons">
  <thead>
    <tr>
      <th>Ano</th>
      <th>Cod Curso</th>
      <th>Curso</th>
      <th>Cod Hab</th>
      <th>Habilitação</th>
      <th>Tipo ingresso</th>
      <th>Tot. pontos</th>
      <th>Acao afirmativa</th>
      <th>Identificação</th>
      <th>Sexo</th>
      <th>Nascimento</th>
      <th>Orígem</th>
      <th>Estado</th>
      <th>Data do encerramento</th>
      <th>Tipo encerramento</th>
      <th>Benefício</th>
      <th>Disc. Reprovadas</th>
      <th>Disc. Aprovadas</th>
      <th>Media pond (suja)</th>
      <th>Media pond (limpa)</th>
      @foreach ($disciplinasDeInteresse as $di)
        <th>{{ $di }}</th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    @foreach ($alunos as $aluno)
      <tr>
        <td>{{ $aluno['ano'] }}</td>
        <td>{{ $aluno['codcur'] }}</td>
        <td>{{ $aluno['nomcur'] }}</td>
        <td>{{ $aluno['codhab'] }}</td>
        <td>{{ $aluno['nomhab'] }}</td>
        <td>{{ $aluno['tiping'] }}</td>
        <td>{{ $aluno['ptoing'] }}</td>
        <td>{{ $aluno['sglacaafm'] }}</td>
        <td>{{ $aluno['codpes'] }}</td>
        <td>{{ $aluno['sexpes'] }}</td>
        <td>{{ $aluno['anonas'] }}</td>
        <td>{{ $aluno['tipdocidf'] }}/{{ $aluno['origem'] }}</td>
        <td>{{ $aluno['status'] }}</td>
        <td>{{ $aluno['data4'] }}</td>
        <td>{{ $aluno['tipenchab'] }}</td>
        <td>{{ $aluno['beneficio'] }}</td>
        <td>{{ $aluno['totalDiscRepr'] }}</td>
        <td>{{ $aluno['totalDiscAprov'] }}</td>
        <td>{{ $aluno['mediaPonderadaSuja'] }}</td>
        <td>{{ $aluno['mediaPonderadaLimpa'] }}</td>
        @foreach ($disciplinasDeInteresse as $di)
          <td>{{ $aluno['di_' . $di] }}</td>
        @endforeach
      </tr>
    @endforeach
  </tbody>
</table>



