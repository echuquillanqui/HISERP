@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h4 class="mb-3">Nuevo paquete</h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('packages.store') }}" method="POST">
        @csrf
        @php($package = new \App\Models\Package())
        @include('packages._form')
    </form>
</div>
@endsection
