<div class="col-12">
    <h2>{{$type}}</h2>
    @foreach($result as $item)
        <div class="row form-group">
            <label
                class="col-4 col-form-label text-md-right">{{$item['name']}}:</label>
            <div class="col-6">
                <div class="form-control-plaintext">
                    @foreach($item['inputs'] as $var)
                        {{$var['name']}} : {{$var['type']}}<br/>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>
