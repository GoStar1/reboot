<div class="modal fade" id="modal-add-template">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Template</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="templateForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="template">Template</label>
                                <div class="col-md-10">
                                    <select id="template" name="template" class="select2" style="width: 100%;">
                                        @foreach($template_dict as $key=>$value)
                                            <option value="{{$key}}">{{$value}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="method">Method</label>
                                <div class="col-md-10">
                                    <select id="method" name="method" class="select2" style="width: 100%;">
                                    </select>
                                </div>
                            </div>
                            <label>Save Data:</label>
                            <div id="save_data"></div>
                            <button type="button" class="btn btn-secondary" id="AddSaveData">AddSaveData</button>
                            <label>calculator:</label>
                            <code>amount*10^decimal*multiplied</code>
                            <div class="form-group mb-0">
                                amount
                                <div class="col-md-10">
                                    <input step="0.000001" class="form-group mb-0" id="cal-number" type="number">
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                decimal
                                <div class="col-md-10">
                                    <input class="form-group mb-0" id="cal-decimal" type="number">
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                multiplied
                                <div class="col-md-10">
                                    <input step="0.000001" class="form-group mb-0" id="cal-multiplied" type="number"
                                           value="1">
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                result
                                <div class="col-md-10">
                                    <div class="form-control-plaintext mb-0" id="cal-result"
                                         style="max-width: 300px;word-break: break-all;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Params:</label>
                            <div id="abis">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="save">Save</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

@section('script_plus')
    <script>
        $('#make_order').change(function () {
            if ($(this).is(':checked')) {
                $('#make_order_list').show();
            } else {
                $('#make_order_list').hide();
            }
        });
        $('#take_order').change(function () {
            if ($(this).is(':checked')) {
                $('#take_order_list').show();
            } else {
                $('#take_order_list').hide();
            }
        });
    </script>
    <script>
        const cal_number = $('#cal-number');
        const cal_decimal = $('#cal-decimal');
        const cal_multiplied = $('#cal-multiplied');

        function calculator() {
            let number = cal_number.val();
            let decimal = cal_decimal.val();
            if (!decimal) {
                decimal = 0;
            }
            let multiplied = cal_multiplied.val();
            $('#cal-result').text(BigNumber(number).multipliedBy(BigNumber(10).pow(decimal)).multipliedBy(multiplied).toFixed(0));
        }

        cal_number.on('input', calculator);
        cal_multiplied.on('input', calculator);
        cal_decimal.on('input', calculator);
        $("#main-form").ajaxForm({
            beforeSubmit: function (arr, $form, options) {
                console.log({arr, $form, options})
                $formArr = $form.serializeObject();
                if ($formArr['inRange'].trim() && !$formArr['startTime']) {
                    toastr.error('when inRange is not empty,startTime can not be null')
                    return false;
                }
            },
            success: function (res) {
                if (res.code === 0) {
                    location.href = "{{route('task_data.index')}}";
                } else {
                    toastr.error(res.msg);
                }
            },
            error: function (res, data) {
                console.log(res, data);
                if (res.responseJSON) {
                    toastr.error(res.responseJSON.message);
                    return;
                }
                if (res.responseText) {
                    toastr.error(res.responseText);
                }
            },
        });
        const method_data = JSON.parse('{!!json_encode($method_data)!!}');
        const $template = $("#template");
        const $method = $("#method");
        $template.change(function () {
            var methods = method_data[$(this).val()];
            if (!methods) {
                return;
            }
            var html = '';
            for (let i = 0; i < methods.length; i++) {
                html += '<option value="' + methods[i] + '">' + methods[i] + '</option>';
            }
            $("#method").html(html);
            setTimeout(function () {
                $method.change();
            }, 1000);
        });
        $template.change();
        $method.change(function () {
            $.ajax({
                url: '{{route('task_data.abi')}}?method=' + $(this).val() + '&template_id=' + $('#template').val(),
                type: 'GET',
                success: function ({code, data, msg}) {
                    if (code === 0) {
                        let abis = '';
                        for (let i = 0; i < data['inputs'].length; i++) {
                            let input = data['inputs'][i];
                            if (data['inputs'][i]['type'] === 'tuple') {
                                for (let j = 0; j < input['components'].length; j++) {
                                    let component = input['components'][j];
                                    let _name = input['name'] + '.' + component['name'];
                                    abis += `<div class="form-group">
                        <label for="priority">${_name}</label>
                    <div class="col-md-12">
                        <input id="${_name}" placeholder="${component['type']}" type="text" class="form-control"
                               name="params[${_name}]" value=""></div></div>`;
                                }
                            } else {
                                abis += `<div class="form-group">
                        <label for="priority">${input['name']}</label>
                    <div class="col-md-12">
                        <input id="${input['name']}" placeholder="${input['type']}" type="text" class="form-control"
                               name="params[${input['name']}]" value=""></div></div>`;
                            }
                        }
                        $("#abis").html(abis);
                    } else {
                        toastr.error(msg);
                    }
                },
                error: function (res) {
                    console.log(res);
                    if (res.responseJSON) {
                        toastr.error(res.responseJSON.message);
                        return;
                    }
                    if (res.responseText) {
                        toastr.error(res.responseText);
                    }
                },
            })
        })
        const save_data_ele = $("#save_data");
        $('#AddSaveData').click(function () {
            save_data_ele.append(`<div class="form-group">
                            <div class="col-md-12 input-group">
                                <input placeholder="" type="text" class="form-control"
                                       name="saveData[]" value="">
                                <span class="input-group-append">
                                    <button type="button" class="remove-btn">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </span>
                            </div>
                        </div>`);
        });
        save_data_ele.on('click', '.remove-btn', function () {
            $(this).closest('.form-group').remove();
        });
        const templates = $("#templates");
        templates.on('click', '.close', function () {
            $(this).closest('.card').closest('.row').remove();
        });

        function checkTemplateForm(event) {
            let index = $('#templates').children().length;
            event.preventDefault();
            const formData = $(event.target).serializeObject();
            const saveData = formData['saveData[]'];
            let save_data_input = '';
            let save_data_text = '';
            if (saveData) {
                if (typeof saveData === 'string') {
                    save_data_input += `<input type="hidden" name="saveData[${index}][]" value="${saveData.trim()}">`;
                    save_data_text += saveData.trim() + "\n";
                } else {
                    for (let i = 0; i < saveData.length; i++) {
                        if (saveData[i].trim() === '') {
                            toastr.error("Save Data Content can not be null");
                            return false;
                        }
                        save_data_input += `<input type="hidden" name="saveData[${index}][]" value="${saveData[i].trim()}">`;
                        save_data_text += saveData[i].trim() + "\n";
                    }
                }
            }
            let params_text = '';
            let params_input = '';
            let template_name = '';
            $('#template').find('option').each(function () {
                if ($(this).val() === formData['template']) {
                    template_name = $(this).text();
                }
            });
            for (let key in formData) {
                if (key.startsWith('params[')) {
                    let name = key.substring(7, key.length - 1);
                    if (formData[key].trim() === '') {
                        toastr.error(name + " Content can not be null");
                        return false;
                    }
                    params_text += name + ':' + formData[key].trim() + "\n";
                    params_input += `<input type="hidden" name="params[${index}][${name}]" value="${formData[key].trim()}">`
                }
            }
            let str = `<div class="row">
                                <div class="col-md-8 offset-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-group row">
                                                <label class="col-md-2 col-form-label text-md-right">Template</label>
                                                <div class="col-md-10">
                                                    <select class="form-control"
                                                            name="template[${index}]" readonly>
                                                        <option value="${formData['template']}">${template_name}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-form-label text-md-right">Method</label>
                                                <div class="col-md-10">
                                                    <select class="form-control"
                                                            name="method[${index}]" readonly>
                                                        <option value="${formData['method']}">${formData['method']}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-form-label text-md-right">Params</label>
                                                ${params_input}
                                                <div class="col-md-10" style="white-space: pre-line;">
                                                    ${params_text}
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-md-2 col-form-label text-md-right">Save Data</label>
                                                ${save_data_input}
                                                <div class="col-md-10" style="white-space: pre-line;">
                                                    ${save_data_text}
                                                </div>
                                            </div>
                                            <button type="button" class="close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
            templates.append(str);
            $(event.target).resetForm();
            save_data_ele.html('');
            $("#modal-add-template").modal('hide');
            setTimeout(function () {
                $template.select2();
                $template.change();
            }, 1000);
        }

        $('#startTime').datetimepicker({
            icons: {time: 'far fa-clock'},
            timeZone: 'UTC',
            showTimezone: true,
            minDate: moment.utc(),
            default: moment.utc(),
            format: 'MM/DD/YYYY HH:mm',
        });
        $('#inRangeTypeList').on('click', '.dropdown-item', function () {
            $('#inRangeTypeShow').html($(this).text());
            $('#inRangeType').val($(this).text());
        });
        document.getElementById("templateForm")
            .addEventListener("submit", checkTemplateForm);
    </script>
@endsection
