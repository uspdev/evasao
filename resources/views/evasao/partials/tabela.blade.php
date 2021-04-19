<table class="table table-bordered table-hover table-sm alunos">
    <thead>
        <tr>
            <th>Ano</th>
            <th>Curso/habilitação</th>
            <th>Tipo ingresso</th>
            <th>Identificação</th>
            <th>Estado</th>
            <th>Tipo encerramento</th>
            <th>Benefício</th>
            <th>Disc. Reprovadas</th>
            <th>Disc. Aprovadas</th>
            <th>Media pond (suja)</th>
            <th>Media pond (limpa)</th>
        </tr>
    </thead>
    <tbody>
    @foreach($alunos as $aluno)
    <tr>
    <td>{{ $aluno['ano'] }}</td>
    <td>{{ $aluno['curso'] }}</td>
    <td>{{ $aluno['tiping'] }}</td>
    <td>{{ $aluno['codpes'] }}</td>
    <td>{{ $aluno['status'] }}</td>
    <td>{{ $aluno['tipenchab'] }}</td>
    <td>{{ $aluno['beneficio'] }}</td>
    <td>{{ $aluno['totalDiscRepr'] }}</td>
    <td>{{ $aluno['totalDiscAprov'] }}</td>
    <td>{{ $aluno['mediaPonderadaSuja'] }}</td>
    <td>{{ $aluno['mediaPonderadaLimpa'] }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

@section('javascripts_bottom')
@parent
<script>
    $(function() {
        oTable = $('.alunos').DataTable({
            dom: 'fBi'
            , "paging": false
            , "order": [
                [1, "asc"]
            ]
            , language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json'
            }
            , "buttons": [
                'excelHtml5'
                , 'csvHtml5'
            ]
        });

    });

</script>
@endsection