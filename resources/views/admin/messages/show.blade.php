@extends('admin.layouts.app')
@section('meta_title', 'Message')

@section('content')
<div class="page-header">
    <div class="page-header-left"><h1 class="page-title">{{ $message->subject ?: 'Message' }}</h1><p class="page-subtitle">From {{ $message->name }} · {{ $message->created_at->format('d M Y, H:i') }}</p></div>
    <div class="page-header-right"><a href="{{ route('admin.messages.index') }}" class="btn-admin-secondary"><i class="fas fa-arrow-left"></i> Back</a></div>
</div>
<div class="admin-card" style="max-width:820px">
    <div class="admin-card-body">
        <p class="mb-1"><strong>{{ $message->name }}</strong> &lt;<a href="mailto:{{ $message->email }}">{{ $message->email }}</a>&gt;@if($message->phone) · {{ $message->phone }}@endif</p>
        @if($message->user)<p class="text-muted">Registered customer: <a href="{{ route('admin.users.show', $message->user_id) }}">{{ $message->user->name }}</a></p>@endif
        <hr>
        <div style="white-space:pre-wrap">{{ $message->message }}</div>
    </div>
    <div class="admin-card-footer d-flex gap-2">
        <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: ' . ($message->subject ?: 'Your message to CityDrug')) }}" class="btn-admin-primary"><i class="fas fa-reply"></i> Reply by email</a>
        <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')<button class="btn-admin-danger">Delete</button></form>
    </div>
</div>
@endsection
