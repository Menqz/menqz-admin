<div class="nav-tabs-custom no-border-radius px-2" >
    <ul class="nav nav-tabs">

        @foreach($partObj->getParts() as $part)
            <li class="nav-item">
                <button class="nav-link {{ $part['active'] ? 'active' : '' }}"
                    {{-- href="#tab-part-{{ $part['id'] }}" --}}
                    data-bs-toggle="tab"
                    data-bs-target="#tab-part-{{ $part['id'] }}"
                    data-url="{{ route('admin.handle-part') }}?class={{ urlencode($part['class']) }}&parent_id={{ $part['parentId'] }}&parent_class={{ urlencode($part['parentClass']) }}">
                    {{ $part['title'] }} <i class="icon-exclamation-circle text-red hide"></i>
                </button>
            </li>
        @endforeach

    </ul>
    <div class="tab-content fields-group">

        @foreach($partObj->getParts() as $part)
            <div class="tab-pane {{ $part['active'] ? 'active' : '' }} px-2" id="tab-part-{{ $part['id'] }}">

            </div>
        @endforeach

    </div>
</div>

<script>
    var loading = `
        <div class="loading text-center" style="padding: 50px;">
            <i class="spinner-border" role="status"></i>
        </div>
    `;
    function loadPart(url, container) {
        container.innerHTML = loading;
        fetch(url)
            .then(response => response.text())
            .then(data => {
                container.innerHTML = data;
            });
    }
    document.addEventListener('DOMContentLoaded', function () {
        // Tab click handler
        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (tabLink) {
            tabLink.addEventListener('click', function (e) {
                e.preventDefault();
                var target = e.target.dataset.bsTarget;
                var url = e.target.dataset.url;
                var container = document.querySelector(target);

                loadPart(url, container);
            });
        });

        // Trigger load for the active tab on page load
        var activeTab = document.querySelector('.nav-tabs .nav-item .nav-link.active[data-bs-toggle="tab"]');
        if (activeTab) {
            var target = activeTab.dataset.bsTarget;
            var url = activeTab.dataset.url;
            var container = document.querySelector(target);
            loadPart(url, container);
        }
    });
</script>
