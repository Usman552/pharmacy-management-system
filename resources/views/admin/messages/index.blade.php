@extends('admin.layouts.app')
@section('meta_title', 'Messages')

@section('content')
<div class="page-header"><div class="page-header-left"><h1 class="page-title">Messages</h1><p class="page-subtitle">Messages sent from the website contact form.</p></div></div>
<div class="admin-card">
    <div style="overflow-x:auto"><table class="admin-table">
        <thead><tr><th>From</th><th>Topic</th><th>Message</th><th>Received</th><th></th></tr></thead>
        <tbody>
        @forelse($messages as $m)
            <tr style="{{ $m->read_at ? '' : 'font-weight:700' }}">
                <td>{{ $m->name }}<div class="text-muted" style="font-size:12px;font-weight:500">{{ $m->email }}</div></td>
                <td>{{ $m->subject ?: '—' }}</td>
                <td>{{ Str::limit($m->message, 80) }}</td>
                <td>{{ $m->created_at->diffForHumans() }}</td>
                <td style="text-align:right"><a href="{{ route('admin.messages.show', $m) }}" class="btn-admin-view">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="empty-state"><i class="fas fa-envelope-open"></i>No messages yet.</div></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($messages->hasPages())<div class="admin-card-footer">{{ $messages->links() }}</div>@endif
</div>
@endsection
