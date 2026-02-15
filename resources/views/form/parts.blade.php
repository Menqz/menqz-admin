<div class="nav-tabs-custom no-border-radius px-2" >
    <ul class="nav nav-tabs nav-parts">

        @foreach($partObj->getParts() as $part)
            <li class="nav-item">
                <button class="nav-link {{ $part['active'] ? 'active' : '' }}"
                    {{-- href="#tab-part-{{ $part['id'] }}" --}}
                    data-bs-toggle="tab"
                    data-bs-target="#tab-part-{{ $part['id'] }}"
                    data-part-id="{{ $part['id'] }}"
                    data-url="{{ route('admin.handle-part') }}?class={{ urlencode($part['class']) }}&parent_id={{ $part['parentId'] }}&parent_class={{ urlencode($part['parentClass']) }}">
                    {{ $part['title'] }} <i class="icon-exclamation-circle text-red hide"></i>
                </button>
            </li>
        @endforeach

    </ul>
    <div class="tab-content fields-group">

        @foreach($partObj->getParts() as $part)
            <div class="tab-pane {{ $part['active'] ? 'active' : '' }} px-2"
                id="tab-part-{{ $part['id'] }}"
                data-part-id="{{ $part['id'] }}"
                data-url="{{ route('admin.handle-part') }}?class={{ urlencode($part['class']) }}&parent_id={{ $part['parentId'] }}&parent_class={{ urlencode($part['parentClass']) }}">

            </div>
        @endforeach

    </div>
</div>

<script>
    admin.form.part.init();

    @foreach($partObj->getParts() as $part)
        admin.form.part.add({
            url: '{{ route('admin.handle-part') }}',
            id_part: '{{ $part['id'] }}',
            main_class: '{{ urlencode($part['class']) }}',
            parent_id: '{{ $part['parentId'] }}',
            parent_class: '{{ urlencode($part['parentClass']) }}',
            active: @if ($part['active']) true @else false @endif,
        });
    @endforeach

</script>
