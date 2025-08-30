@extends('layouts.main')

@section('title', 'Recuperar Senha')

@section('content')
    <h1>Recuperar Senha</h1>

    @if(session('status'))
        <div style="color:green">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required />
        </div>

        <div>
            <button type="submit">Enviar link de recuperação</button>
        </div>
    </form>
@endsection
