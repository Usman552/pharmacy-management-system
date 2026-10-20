@extends('admin.layouts.app')
@section('meta_title', 'Create Blog Post')

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
        <h1 class="page-title">Create Blog Post</h1>
        <p class="page-subtitle">Write an SEO-optimized health article</p>
    </div>
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Blogs
    </a>
</div>

<form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data" id="blogForm">
@csrf
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
                           value="{{ old('title') }}"
                           placeholder="Enter an engaging, SEO-friendly title..."
                           oninput="updateSeoPreview()">
                    <div class="char-counter"><span id="titleCount">0</span>/60 characters recommended</div>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Short Excerpt</label>
                    <textarea name="excerpt" id="postExcerpt" class="form-control" rows="3"
                              placeholder="Brief summary shown in blog listing (140-160 chars ideal)..."
                              oninput="updateSeoPreview()">{{ old('excerpt') }}</textarea>
                    <div class="char-counter"><span id="excerptCount">0</span>/160 characters</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Content <span class="text-danger">*</span></label>
                    <textarea name="content" id="blogContent"
                              class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-search me-2"></i>SEO Settings</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-600">SEO Title <small class="text-muted">(leave blank to use post title)</small></label>
                    <input type="text" name="meta_title" id="metaTitle" class="form-control"
                           value="{{ old('meta_title') }}" placeholder="SEO title..." oninput="updateSeoPreview()">
                    <div class="char-counter"><span id="metaTitleCount">0</span>/60</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Meta Description</label>
                    <textarea name="meta_description" id="metaDesc" class="form-control" rows="3"
                              placeholder="140-160 characters recommended..." oninput="updateSeoPreview()">{{ old('meta_description') }}</textarea>
                    <div class="char-counter"><span id="metaDescCount">0</span>/160</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control"
                           value="{{ old('meta_keywords') }}"
                           placeholder="health tips, pakistan pharmacy, medicines online, ...">
                    <small class="text-muted">Comma-separated keywords</small>
                </div>
                <label class="form-label fw-600">Google Search Preview</label>
                <div class="seo-preview">
                    <div class="sp-title" id="previewTitle">Post Title Goes Here</div>
                    <div class="sp-url">citydrug.pk/blog/post-slug</div>
                    <div class="sp-desc" id="previewDesc">Your meta description will appear here in search results.</div>
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
                        <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1" {{ old('is_published') ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="isPublished">Published (visible on site)</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label fw-600" for="isFeatured">Featured Post</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Category</label>
                    <select name="category" class="form-select">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Tags</label>
                    <input type="text" name="tags" class="form-control"
                           value="{{ old('tags') }}" placeholder="vitamins, health, pharmacy">
                    <small class="text-muted">Comma-separated tags</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-save me-2"></i>Save Blog Post
                </button>
                <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-image me-2"></i>Featured Image</h6></div>
            <div class="card-body">
                <div id="imgArea" onclick="document.getElementById('blogImage').click()"
                     style="cursor:pointer;border:2px dashed #cbd5e1;border-radius:10px;padding:24px;text-align:center">
                    <div id="imgPreview">
                        <i class="fas fa-cloud-upload-alt fs-2 text-muted"></i>
                        <p class="text-muted mt-2 mb-0" style="font-size:13px">Click to upload</p>
                        <small class="text-muted">JPG, PNG — recommended 1200×630px</small>
                    </div>
                </div>
                <input type="file" name="image" id="blogImage" class="d-none" accept="image/*" onchange="previewImg(this)">
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-lightbulb me-2 text-warning"></i>SEO Tips</h6></div>
            <div class="card-body" style="font-size:13px">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Title: 50–60 characters</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Meta desc: 140–160 chars</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use keyword in title</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Write 500+ words</li>
                    <li><i class="fas fa-check text-success me-2"></i>Add tags for discovery</li>
                </ul>
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
        placeholder: 'Write your full article here. Use headings, bold, lists and images for better SEO...',
        tabsize: 2,
        height: 420,
        toolbar: [
            ['style',  ['style']],
            ['font',   ['bold','italic','underline','strikethrough','clear']],
            ['fontsize',['fontsize']],
            ['color',  ['color']],
            ['para',   ['ul','ol','paragraph']],
            ['table',  ['table']],
            ['insert', ['link','picture','hr']],
            ['view',   ['fullscreen','codeview','help']]
        ],
        callbacks: {
            onChange: function(contents) {
                // Keep textarea in sync for form submission
                $('#blogContent').val(contents);
            }
        }
    });
});

function updateSeoPreview() {
    var title   = document.getElementById('postTitle').value;
    var metaT   = document.getElementById('metaTitle').value;
    var metaD   = document.getElementById('metaDesc').value;
    var excerpt = document.getElementById('postExcerpt').value;

    document.getElementById('previewTitle').textContent = metaT || title || 'Post Title Goes Here';
    document.getElementById('previewDesc').textContent  = metaD || excerpt || 'Your meta description will appear here.';

    var tc = document.getElementById('titleCount');
    var mc = document.getElementById('metaTitleCount');
    var dc = document.getElementById('metaDescCount');
    var ec = document.getElementById('excerptCount');

    tc.textContent = title.length;
    mc.textContent = metaT.length;
    dc.textContent = metaD.length;
    ec.textContent = excerpt.length;

    tc.className = title.length > 60 ? 'char-danger' : '';
    mc.className = metaT.length > 60 ? 'char-danger' : '';
    dc.className = metaD.length > 160 ? 'char-danger' : '';
    ec.className = excerpt.length > 160 ? 'char-danger' : '';
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
