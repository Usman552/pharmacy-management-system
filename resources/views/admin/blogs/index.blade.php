@extends('admin.layouts.app')
@section('meta_title', 'Blog Posts')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Blog Posts</h1>
        <p class="page-subtitle">Manage your health articles and blog content</p>
    </div>
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i> New Blog Post
    </a>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-blog"></i></div>
            <div><div class="stat-value">{{ \App\Models\Blog::count() }}</div><div class="stat-label">Total Posts</div></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-value">{{ \App\Models\Blog::where('is_published',true)->count() }}</div><div class="stat-label">Published</div></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-edit"></i></div>
            <div><div class="stat-value">{{ \App\Models\Blog::where('is_published',false)->count() }}</div><div class="stat-label">Drafts</div></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-eye"></i></div>
            <div><div class="stat-value">{{ number_format(\App\Models\Blog::sum('views')) }}</div><div class="stat-label">Total Views</div></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="d-flex gap-3 flex-wrap">
            <input type="text" name="q" class="form-control" style="max-width:250px" placeholder="Search title..." value="{{ request('q') }}">
            <select name="status" class="form-select" style="max-width:160px" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="published" {{ request('status')==='published'?'selected':'' }}>Published</option>
                <option value="draft"     {{ request('status')==='draft'?'selected':'' }}>Drafts</option>
            </select>
            <button class="btn btn-outline-secondary">Filter</button>
            @if(request()->anyFilled(['q','status']))<a href="{{ route('admin.blogs.index') }}" class="btn btn-light">Clear</a>@endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:50%">Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blogs as $blog)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($blog->image)
                                <img src="{{ asset('images/blogs/'.$blog->image) }}" alt="" style="width:45px;height:45px;object-fit:cover;border-radius:8px">
                                @else
                                <div style="width:45px;height:45px;background:#f0f4ff;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px">📋</div>
                                @endif
                                <div>
                                    <div class="fw-600">{{ Str::limit($blog->title, 55) }}</div>
                                    <small class="text-muted">By {{ $blog->author->name ?? 'Admin' }} · {{ $blog->reading_time }} min read</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark">{{ $blog->category }}</span></td>
                        <td>
                            @if($blog->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                            @if($blog->is_featured)
                                <span class="badge bg-warning text-dark ms-1">Featured</span>
                            @endif
                        </td>
                        <td>{{ number_format($blog->views) }}</td>
                        <td><small class="text-muted">{{ $blog->created_at->format('d M Y') }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($blog->is_published)
                                <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-sm btn-light" title="View" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('Delete this blog post?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="fs-1">📝</div>
                            <p class="text-muted mt-2">No blog posts yet.</p>
                            <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">Create First Post</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $blogs->withQueryString()->links() }}
</div>

@endsection
