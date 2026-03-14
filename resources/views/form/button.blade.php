@if(isset($positionFooter) && $positionFooter)

    <div class="btn-group ms-2 d-flex d-md-inline-flex my-1">
        <button type="button"
                data-label-button="{{ $label }}"
                class="btn btn-primary py-3 py-md-2 {{ $class }} {{ !$useUnlockButton ? 'w-100' : 'flex-grow-1' }}"
                @if($useUnlockButton) disabled @endif
                {!! $attributes !!}>
            {!! $label !!}
        </button>
        @if($useUnlockButton)
            <button type="button"
                    class="unlock-{{ $id }} btn btn-primary py-3  py-md-2"
                    data-ref="{{ $id }}"
                    style="margin-left: -3px">
                <i class="icon-lock"></i>
            </button>
        @endif
    </div>
@else
    <div class="{{$viewClass['form-group']}}">
        <label class="{{$viewClass['label']}} form-label"></label>
        <div class="{{$viewClass['field']}}">
            <input type='button' value='{{$label}}' class="btn {{ $class }}" {!! $attributes !!} />
        </div>
    </div>
@endif
