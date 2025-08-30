@extends('layouts.main')

@section('title', 'Login')

@section('content')
    <h1>Login</h1>

    @if(session('status'))
        <div style="color:green">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div style="color:red">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus />
        </div>

        <div>
            <label for="senha">Senha</label>
            <input id="senha" name="senha" type="password" required />
        </div>



        <div>
            <button type="submit">Entrar no Sistema</button>
        </div>
    </form>


@endsection
