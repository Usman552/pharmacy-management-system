{{-- Real product photo when one exists on disk, otherwise one consistent fallback tile. --}}
@php($size = $size ?? '')
<div class="img-box {{ $size }}">
    @if($product->image_url)
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" width="400" height="400">
    @else
        <div class="fallback" role="img" aria-label="{{ $product->name }} (image coming soon)">
            <i class="fas {{ $product->category?->icon_class ?? 'fa-capsules' }}"></i>
            <span>{{ $product->category?->name ?? 'Pharmacy' }}</span>
        </div>
    @endif
</div>
