@extends('layouts.user')
@section('content')
<view-ticket   :data="{{ ($ticketId) }}"></view-ticket>
@endsection