<div class="col-12">
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">ID:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['task_trans_id']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Chain:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['chain']->name}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Task Data:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$task_data_name}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Template:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$template_name}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Method:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['method']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Args:</label>
        <div class="col-6">
            <div class="form-control-plaintext"
                 style="word-break: break-all;max-width: 200px;">{{json_encode($item['args'])}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Call Data:</label>
        <div class="col-6">
            <div class="form-control-plaintext"
                 style="word-break: break-all;max-width: 200px;">{{$item['call_data']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Amount:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['amount']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Max Gas Price:</label>
        <div class="col-6">
            <div class="form-control-plaintext"
                 style="word-break: break-all;max-width: 200px;">{{$min_gas_price}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Logs:</label>
        <div class="col-6">
            <div class="form-control-plaintext"
                 style="word-break: break-all;max-width: 200px;">{{json_encode($item['logs'])}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Execute Time:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{date('Y-m-d H:i:s',$item['execute_time'])}}</div>
        </div>
    </div>
    @if($item['error'])
        <div class="row form-group">
            <label
                class="col-4 col-form-label text-md-right">Error:</label>
            <div class="col-6">
                <div class="form-control-plaintext"
                     style="word-break: break-all;max-width: 200px;">{{$item['error']}}</div>
            </div>
        </div>
    @endif
    @if($item['send_trans_time'])
        <div class="row form-group">
            <label
                class="col-4 col-form-label text-md-right">Send Trans Time:</label>
            <div class="col-6">
                <div class="form-control-plaintext">{{date('Y-m-d H:i:s',$item['send_trans_time'])}}</div>
            </div>
        </div>
    @endif
</div>
