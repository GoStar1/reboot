<div class="col-12">
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">ID:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['task_id']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Chain:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{\App\Enums\Chain::from($item['chain'])->name}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Task Data:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['task_name']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Template:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['template_name']}}</div>
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
        <div class="col-6" style="word-break: break-all;max-width: 200px;">
            <div class="form-control-plaintext">{{$item['args']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Call Data:</label>
        <div class="col-6" style="word-break: break-all;max-width: 200px;">
            <div class="form-control-plaintext">{{$item['call_data']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Max Gas Price:</label>
        <div class="col-6" style="word-break: break-all;max-width: 200px;">
            <div class="form-control-plaintext">{{$item['min_gas_price']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Save Data:</label>
        <div class="col-6">
            <div class="form-control-plaintext"
                 style="word-break: break-all;max-width: 200px;">{{$item['save_data']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Start Time:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{date('Y-m-d H:i:s',$item['start_time'])}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">In Range:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{\App\Services\SystemUtils::secondsDiff($item['time_range'])}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Failed:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['failed']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Complete:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['completed']}}</div>
        </div>
    </div>
    <div class="row form-group">
        <label
            class="col-4 col-form-label text-md-right">Total:</label>
        <div class="col-6">
            <div class="form-control-plaintext">{{$item['accounts']}}</div>
        </div>
    </div>
</div>
