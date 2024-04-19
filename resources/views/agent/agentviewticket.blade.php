@extends('layouts.agent')
@section('content')
<agent-view-ticket   :data="{{ ($ticketId) }}"></agent-view-ticket>
@endsection