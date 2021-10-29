@extends('layouts.app')

@section('content')

<div class="h4">
    Lista de alunos que fizeram reingresso no mesmo curso, desde 2010.
</div>

  <table class="table table-bordered">
      <tr>
          @foreach($reingresso[0] as $col=>$data)
          <th>{{ $col }}</th>
          @endforeach
      </tr>
    @foreach ($reingresso as $row)
      <tr>
        @foreach ($row as $col)
          <td>{{ $col }}</td>
        @endforeach
      </tr>
    @endforeach
  </table>

@endsection
