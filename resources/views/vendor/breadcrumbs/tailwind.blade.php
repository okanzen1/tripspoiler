@unless ($breadcrumbs->isEmpty())
<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex flex-wrap items-center text-sm text-slate-500">
        @foreach ($breadcrumbs as $breadcrumb)
            <li class="flex items-center">
                @if ($breadcrumb->url && !$loop->last)
                    <a href="{{ $breadcrumb->url }}"
                       class="hover:text-[#C62E2E] transition-colors">
                        {{ $breadcrumb->title }}
                    </a>
                    <span class="mx-2 text-slate-400">/</span>
                @else
                    <span class="text-slate-700 font-medium">
                        {{ $breadcrumb->title }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endunless