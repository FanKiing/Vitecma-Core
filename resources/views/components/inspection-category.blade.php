@props(['category'])
<span class="badge inspection-category inspection-category--{{ $category }}">{{ $category === 'PL' ? '🚛' : '🚗' }} {{ $category }}</span>
