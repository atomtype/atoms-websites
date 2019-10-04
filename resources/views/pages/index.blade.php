@extends('layouts.app')

@section('content')
        <div class="jumbotron text-center">
                <h1>{{$title}}</h1>
                <p>Laravel app from youtube</p>
                <a href="/login" class="btn btn-primary">Login</a>&nbsp;<a href="/register" class="btn btn-success">Register</a>
        </div>
@endsection