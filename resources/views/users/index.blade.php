@extends('statamic::layout')
@section('title', __('Users'))
@section('wrapper_class', 'max-w-full')

@section('content')

    <header class="flex mb-3">
        <h1 class="flex-1">
            {{ __('Users') }}
        </h1>

        @can('create', 'Statamic\Contracts\Auth\User')
            <a href="{{ cp_route('users.create') }}" class="btn-primary">{{ __('Create User') }}</a>
        @endcan
    </header>

    <user-listing
        listing-key="users"
        initial-sort-column="email"
        initial-sort-direction="asc"
        :filters="{{ $filters->toJson() }}"
        action-url="{{ cp_route('users.actions') }}"
    ></user-listing>

@endsection
