@extends('layouts.app')
@section('content')
<div>
  <ul class="list-group">
    <li class="list-group-item">
      Joined on {{$user->created_at->format('M d,Y \a\t h:i a') }}
    </li>
    <li class="list-group-item panel-body">
      <table class="table-padding">
        <style>
          .table-padding td{
            padding: 3px 8px;
          }
        </style>
        <tr>
          <td>Name</td>
          <td> {{$user->name}}</td>
        </tr>
        <tr>
          <td>Email</td>
          <td>{{$user->email}}</td>
        </tr>
        <tr>
          <td>API Token</td>
          <td>{{$user->api_token}}</td>
        </tr>
      </table>
    </li>
    <li class="list-group-item">
      
    </li>
  </ul>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
      <h3></h3></div>
  <div class="panel-body">
    
  </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading"><h3></h3></div>
  <div class="list-group">
    
  </div>
</div>
@endsection