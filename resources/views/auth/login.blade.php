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
            <label for="tx_email">Email</label>
            <input id="tx_email" name="tx_email" type="email" value="{{ old('tx_email') }}" required autofocus />
        </div>

        <div>
            <label for="tx_senha">Senha</label>
            <input id="tx_senha" name="tx_senha" type="password" required />
        </div>

        <div>
            <label>
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} /> Lembrar de mim
            </label>
        </div>

        <div>
            <button type="submit">Entrar no Sistema</button>
        </div>
    </form>

    <p>
        <a href="{{ route('password.request') }}">Esqueceu a senha?</a>
        |
        <a href="{{ route('register') }}">Solicitar cadastro</a>
    </p>
@endsection
