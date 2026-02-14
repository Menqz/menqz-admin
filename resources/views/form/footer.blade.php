
<footer class="navbar form-footer navbar-light bg-white py-3 px-4 @if (!empty($fixed_footer))shadow fixed-bottom @endif">
    <div class="row">


    <div class="col-9 d-flex align-items-center flex-row">
        @if (isset($builder))
            <div class="d-none d-md-inline">
                @foreach($builder->fields() as $field)
                    @php
                        $exibeField = false;
                        if(get_class($field) == 'App\Http\Extensions\Form\Field\MBFCustomButton') {
                            if ($field->getPositionFooter()) {
                                $exibeField = true;
                            }
                        }
                    @endphp
                    @if($exibeField)
                        {!! $field->render() !!}
                    @endif
                @endforeach
            </div>
            <div class="dropdown d-md-none mb-2">

                @php
                    $algumBotao = false;
                    foreach($builder->fields() as $field) {
                        if(get_class($field) == 'App\Http\Extensions\Form\Field\MBFCustomButton') {
                            if ($field->getPositionFooter()) {
                                $algumBotao = true;
                                break;
                            }
                        }
                    }
                @endphp
                <div class="btn-group dropup @if(!$algumBotao) d-none @endif">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        Ações
                    </button>
                    <ul class="dropdown-menu" data-bs-auto-close="false" style="width: 300px;">
                        @foreach($builder->fields() as $field)
                            @php
                                $exibeField = false;
                                if(get_class($field) == 'App\Http\Extensions\Form\Field\MBFCustomButton') {
                                    if ($field->getPositionFooter()) {
                                        $exibeField = true;
                                    }
                                }
                            @endphp
                            @if($exibeField)
                                {!! $field->render() !!}
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>

        @endif
        @if(in_array('submit', $buttons))
            <div class="btn-group">
            @foreach($submit_redirects as $value => $redirect)
                @if(in_array($redirect, $checkboxes))
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input after-submit" id="after-save-{{$redirect}}" name="after-save" value="{{ $value }}" {{ ($default_check == $redirect) ? 'checked' : '' }}>
                    <label class="form-check-label" for="after-save-{{$redirect}}">{{ trans("admin.{$redirect}") }}</label>
                </div>
                @endif
            @endforeach
            </div>
        @endif


    </div>
    <div class="col-3 d-flex align-items-center flex-row-reverse">

        @if(in_array('submit', $buttons))
            <div class="btn-group ms-2">
                @foreach($submit_redirects as $value => $redirect)
                    @if(in_array($redirect, $checkboxes))
                        <div class="form-check form-check-inline">
                            <input type="checkbox" class="form-check-input after-submit" id="after-save-{{$redirect}}" name="after-save" value="{{ $value }}" {{ ($default_check == $redirect) ? 'checked' : '' }}>
                            <label class="form-check-label" for="after-save-{{$redirect}}">{{ trans("admin.{$redirect}") }}</label>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="btn-group ms-2">
                <button type="submit" class="btn btn-primary">{{ trans('admin.submit') }}</button>
            </div>
        @endif
        @if(in_array('reset', $buttons))
            <div class="btn-group ms-1"">
                <a href="{{$route_cancel}}" id="btn-cancel" type="button" class="btn btn-secondary">{{ trans('admin.reset') }}</a>
            </div>

            @if($use_destroy_in_cancel)
                <script>
                    document.getElementById('btn-cancel').addEventListener('click', function(e){
                        e.preventDefault();
                        Swal.fire({
                            title: 'Ao cancelar, as informações do registro não serão salvas. Deseja continuar?',
                            icon: "question",
                            showCancelButton: true,
                            confirmButtonText: __('confirm'),
                            cancelButtonText:  __('cancel'),
                        }).then(function (res){
                            if (res.isConfirmed){
                                let route_previous = document.querySelector('._previous_') ? document.querySelector('._previous_').value : undefined;
                                if (typeof route_previous == 'undefined') {
                                    route_previous = '{{$route_cancel}}';
                                }
                                @if ($is_creating)
                                    const id_object = document.querySelector('.id_object') ? document.querySelector('.id_object').value : null;
                                    var rotaDelete = "{{$route_destroy}}";

                                    if (id_object > 0 && rotaDelete != '') {
                                        e.preventDefault();
                                        rotaDelete = rotaDelete.replace('#id#', id_object);
                                        console.log(rotaDelete);

                                        admin.ajax.post(rotaDelete, {
                                            _token: '{{ csrf_token() }}',
                                            _method: 'DELETE'
                                        }, function(data){
                                            console.log(data);
                                            window.location = route_previous;
                                        });
                                    }
                                @else
                                    window.location = route_previous;
                                @endif
                            }
                        });
                    });
                </script>
            @endif
        @endif
    </div>
</div>
</footer>
