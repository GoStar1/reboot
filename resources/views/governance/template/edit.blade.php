@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">

                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <div class="content">
            <div class="row justify-content-left">
                <div class="col-md-8">
                    <form class="form main-form" method="POST" action="{{ route('template.update') }}">
                        @csrf
                        <input type="hidden" value="{{$template_id}}" name="template_id">
                        <div class="form-group row">
                            <label for="chain"
                                   class="col-md-4 col-form-label text-md-right">chain</label>
                            <div class="col-md-6">
                                <select id="chain" name="chain" class="select2" style="width: 100%;">
                                    @foreach(\App\Enums\Chain::cases() as $_chain)
                                        <option value="{{$_chain->value}}"
                                            {{$_chain==$data['chain']?'selected':''}} >{{$_chain->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="contract"
                                   class="col-md-4 col-form-label text-md-right">contract</label>
                            <div class="col-md-6">
                                <input id="contract" type="text" class="form-control"
                                       name="contract" required value="{{$data['contract']??''}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="name"
                                   class="col-md-4 col-form-label text-md-right">name</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control"
                                       name="name" required value="{{$data['name']??''}}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="abi"
                                   class="col-md-4 col-form-label text-md-right">ABI</label>
                            <div class="col-md-6">
                                <textarea style="white-space: pre;" id="abi" class="form-control" rows="10"
                                          cols="10"
                                          name="abi">{{$data['abi']?json_encode($data['abi'],JSON_UNESCAPED_UNICODE| JSON_PRETTY_PRINT):''}}</textarea>
                            </div>
                        </div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script_plus')
    <script>
        $(".main-form").ajaxForm({
            beforeSubmit: function (arr, $form, options) {
                console.log('beforeSubmit',);
                const formData = $form.serializeObject();
                console.log(formData);
                // The array of form data takes the following form:
                // [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
                // return false to cancel submit
                if (formData['name'].trim() === '') {
                    toastr.error("Input name");
                    return false;
                }
                if (formData['contract'].trim() === '') {
                    toastr.error("Input contract");
                    return false;
                }
                if (formData['abi'].trim() === '') {
                    toastr.error("Input abi");
                    return false;
                }
            },
            error: function (err) {
                console.log(err);
                toastr.error(err.toString());
            },
            success: function (res) {
                if (res.code === 0) {
                    alert("success");
                    location.href = "{{route('template.index')}}";
                } else {
                    alert(res.msg);
                }
            }
        });
    </script>
@endsection
