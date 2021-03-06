@extends('layouts.main')

@section('content')

<ol class="breadcrumb">
  <li><a href="/account">My Account</a></li>
  <li><a href="/account/addresses">Addresses</a></li>
  <li class="active">Create Address</li>
</ol>

<h1>Create New Address</h1>
@include('partials.errors')

{!! Form::model($address, ['route' => ['addresses.store'], 'method' => 'POST']) !!}
    @include('addresses.form')
    <input type="submit" class="btn btn-success" value="Save Address">
{!! Form::close() !!}
@stop