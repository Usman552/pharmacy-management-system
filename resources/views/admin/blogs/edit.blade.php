@extends('admin.layouts.app')
@section('meta_title', 'Edit Blog Post')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<style>
.seo-preview { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:16px; }
.seo-preview .sp-title { color:#1a0dab; font-size:18px; margin-bottom:4px; font-family:arial,sans-serif; }
.seo-preview .sp-url   { color:#006621; font-size:13px; margin-bottom:4px; font-family:arial,sans-serif; }
.seo-preview .sp-desc  { color:#545454; font-size:14px; line-height:1.4; font-family:arial,sans-serif; }
.char-counter { font-size:12px; color:#64748b; text-align:right; margin-top:4px; }
.char-danger  { color:#ef4444 !important; }
.note-editor.note-frame { border-radius:10px !important; border:1px solid #e2e8f0 !important; }
.note-toolbar { border-radius:10px 10px 0 0 !important; background:#f8fafc !important; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Blog Post</h1>
        <p class="page-subtitle">{{ Str::limit($blog->title, 60) }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($blog->is_published)
            <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-outline-success" target="_blank">
                <i class="fas fa-eye me-1"></i>Preview
            </a>
        @endif
        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<form action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data" id="blogForm">
@csrf
@method('PUT')
<div class="row g-4">

    {{-- Main Content --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-heading me-2"></i>Post Content</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-600">Post Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="postTitle"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title', $blog->title) }}"
                           oninput="updateSeoPreview()">
                    <div class="char-counter"><span id="titleCount">0</span>/60 characters recommended</div>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Short Excerpt</label>
                    <textarea name="excerpt" id="postExcerpt" class="form-control" rows="3"
                              oninput="updateSeoPreview()">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    <div class="char-counter"><span id="excerptCount">0</span>/160 characters</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Content <span class="text-danger">*</span></label>
                    <textarea name="content" id="blogContent"
                              class="form-control @error('content') is-invalid @enderror">{{ old('content', $blog->content) }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-search me-2"></i>SEO Settings</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-600">SEO Title</label>
                    <input type="text" name="meta_title" id="metaTitle" class="form-control"
                           value="{{ old('meta_title', $blog->meta_title) }}"
                           oninput="updateSeoPreview()">
                    <div class="char-counter"><span id="metaTitleCount">0</span>/60</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Meta Description</label>
                    <textarea name="meta_description" id="metaDesc" class="form-control" rows="3"
                              oninput="updateSeoPreview()">{{ old('meta_description', $blog->meta_description) }}</textarea>
                    <div class="char-counter"><span id="metaDescCount">0</span>/160</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control"
                           value="{{ old('meta_keywords', $blog->meta_keywords) }}">
                    <small class="text-muted">Comma-separated keywords</small>
                </div>
                <label class="form-label fw-600">Google Search Preview</label>
                <div class="seo-preview">
                    <div class="sp-title" id="previewTitle">{{ $blog->meta_title ?: $blog->title }}</div>
                    <div class="sp-url">citydrug.pk/blog/{{ $blog->slug }}</div>
                    <div class="sp-desc"  id="previewDesc">{{ $blog->meta_description ?: $blog->excerpt }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-cog me-2"></i>Publish Settings</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_published"
                               id="isPublished" value="1" {{ $blog->is_published ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="isPublished">Published</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured"
                               id="isFeatured" value="1" {{ $blog->is_featured ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="isFeatured">Featured Post</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Category</label>
                    <select name="category" class="form-select">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $blog->category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Tags</label>
                    <input type="text" name="tags" class="form-control"
                           value="{{ is_array($blog->tags) ? implode(', ', $blog->tags) : '' }}"
                           placeholder="health, vitamins, ...">
                    <small class="text-muted">Comma-separated tags</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Update Post
                </button>
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-image me-2"></i>Featured Image</h6></div>
            <div class="card-body">
                <div id="imgArea" onclick="document.getElementById('blogImage').click()"
                     style="cursor:pointer;border:2px dashed #cbd5e1;border-radius:10px;padding:12px;text-align:center">
                    <div id="imgPreview">
                        @if($blog->image)
                            <img src="{{ asset('images/blogs/'.$blog->image) }}"
                                 style="width:100%;border-radius:8px;max-height:180px;object-fit:cover" alt="">
                            <p class="text-muted mt-2 mb-0" style="font-size:12px">Click to change</p>
                        @else
                            <i class="fas fa-cloud-upload-alt fs-2 text-muted"></i>
                            <p class="text-muted mt-2 mb-0" style="font-size:13px">Click to upload</p>
                        @endif
                    </div>
                </div>
                <input type="file" name="image" id="blogImage" class="d-none" accept="image/*" onchange="previewImg(this)">
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Post Stats</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" style="font-size:13px">Total Views</span>
                    <strong>{{ number_format($blog->views) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" style="font-size:13px">Reading Time</span>
                    <strong>{{ $blog->reading_time ?? 1 }} min</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" style="font-size:13px">Created</span>
                    <strong>{{ $blog->created_at->format('d M Y') }}</strong>
                </div>
                @if($blog->published_at)
                <div class="d-flex justify-content-between">
                    <span class="text-muted" style="font-size:13px">Published</span>
                    <strong>{{ $blog->published_at->format('d M Y') }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
    $('#blogContent').summernote({
        placeholder: 'Write your article content here...',
        tabsize: 2,
        height: 420,
        toolbar: [
            ['style',   ['style']],
            ['font',    ['bold','italic','underline','strikethrough','clear']],
            ['fontsize',['fontsize']],
            ['color',   ['color']],
            ['para',    ['ul','ol','paragraph']],
            ['table',   ['table']],
            ['insert',  ['link','picture','hr']],
            ['view',    ['fullscreen','codeview','help']]
        ]
    });
});

function updateSeoPreview() {
    var title   = document.getElementById('postTitle').value;
    var metaT   = document.getElementById('metaTitle').value;
    var metaD   = document.getElementById('metaDesc').value;
    var excerpt = document.getElementById('postExcerpt').value;

    document.getElementById('previewTitle').textContent = metaT || title || 'Post Title';
    document.getElementById('previewDesc').textContent  = metaD || excerpt || 'Meta description';

    document.getElementById('titleCount').textContent    = title.length;
    document.getElementById('metaTitleCount').textContent = metaT.length;
    document.getElementById('metaDescCount').textContent  = metaD.length;
    document.getElementById('excerptCount').textContent   = excerpt.length;

    document.getElementById('titleCount').className    = title.length > 60  ? 'char-danger' : '';
    document.getElementById('metaTitleCount').className = metaT.length > 60  ? 'char-danger' : '';
    document.getElementById('metaDescCount').className  = metaD.length > 160 ? 'char-danger' : '';
}

function previewImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').innerHTML =
                '<img src="' + e.target.result + '" style="width:100%;border-radius:8px;max-height:180px;object-fit:cover">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
updateSeoPreview();
</script>
@endpush
@endsection
