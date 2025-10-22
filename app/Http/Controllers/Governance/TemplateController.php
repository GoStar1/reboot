<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Http\Controllers\Controller;
use App\Models\Robot\Template;
use Illuminate\Http\Request;
use Str;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $keyword = $request->input('keyword');
        $query = new Template;
        $keyword && $query = $query->where('name', 'like', '%' . $keyword . '%');
        $chain && $query = $query->where('chain', $chain);
        $list = $query->orderByDesc('template_id')->paginate()->toArray();
        foreach ($list['data'] as &$item) {
            $item['chain'] = Chain::from($item['chain']);
            $methods = [];
            foreach ($item['abi'] as $func) {
                if ($func['type'] === 'function' && !Str::startsWith($func['name'], '_') && $func['name'] !== 'initialize') {
                    if (in_array($func['stateMutability'], ['view', 'pure'], true)) {
                        $methods[] = "<span style='color:green;'>{$func['name']}</span>";
                    } else {
                        $methods[] = "<span style='color:red;'>{$func['name']}</span>";
                    }
                }
            }
            $item['methods'] = implode('&nbsp;', $methods);
        }
        unset($item);
        return view('governance.template.index', [
            'active_menu' => 'governance.template',
            'title' => 'templates',
            'list' => $list,
            'keyword' => $keyword,
            'chain' => $chain,
        ]);
    }

    public function read(Request $request)
    {
        $template_id = $request->input('template_id');
        $data = Template::find($template_id);
        $result = [];
        foreach ($data->abi as $func) {
            if ($func['type'] === 'function') {
                if (in_array($func['stateMutability'], ['view', 'pure'], true)) {
                    $result[] = $func;
                }
            }
        }
        return view('governance.template.detail', [
            'result' => $result,
            'type' => 'Read Methods',
        ]);
    }

    public function write(Request $request)
    {
        $template_id = $request->input('template_id');
        $data = Template::find($template_id);
        $result = [];
        foreach ($data->abi as $func) {
            if ($func['type'] === 'function') {
                if (!in_array($func['stateMutability'], ['view', 'pure'], true)) {
                    $result[] = $func;
                }
            }
        }
        return view('governance.template.detail', [
            'result' => $result,
            'type' => 'Write Methods',
        ]);
    }

    public function log(Request $request)
    {
        $template_id = $request->input('template_id');
        $data = Template::find($template_id);
        $result = [];
        foreach ($data->abi as $func) {
            if ($func['type'] === 'event') {
                $result[] = $func;
            }
        }
        return view('governance.template.detail', [
            'result' => $result,
            'type' => 'Logs',
        ]);
    }


    public function edit(Request $request)
    {
        $template_id = $request->input('template_id');
        if ($template_id) {
            $data = Template::find($template_id);
        } else {
            $data = ['abi' => '', 'chain' => null];
        }
        return view('governance.template.edit', [
            'active_menu' => 'governance.template',
            'title' => $template_id ? 'Edit Template' : 'Add Template',
            'data' => $data,
            'template_id' => $template_id,
        ]);
    }

    public function updateData(Request $request): \Illuminate\Http\JsonResponse
    {
        $template_id = $request->input('template_id');
        $chain = Chain::tryFrom($request->input('chain'));
        $name = $request->input('name');
        $abi = json_decode($request->input('abi'), true);
        $contract = $request->input('contract');
        if (!$abi) {
            return $this->error(1, 'input abi');
        }
        if (!$name) {
            return $this->error(1, 'input name');
        }
        if (!$chain) {
            return $this->error(1, 'input chain');
        }
        if (!$contract) {
            return $this->error(1, 'input contract');
        }
        $data = compact(['chain', 'name', 'abi', 'contract']);
        if ($template_id) {
            $ret = Template::where('template_id', $template_id)->update($data);
        } else {
            $ret = (new Template)->forceFill($data)->save();
        }
        return $ret ? $this->success() : $this->error(1, 'fail');
    }
}
