<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Http\Controllers\Controller;
use App\Models\BlockChain\ChainRpc;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Web3\Eth;
use Web3\Providers\HttpProvider;

class RpcController extends Controller
{
    public function index(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $keyword = $request->input('keyword');
        $status = $request->input('status');
        $query = new ChainRpc;
        $status && $query = $query->where('status', $status);
        $keyword && $query = $query->where(function ($query) use ($keyword) {
            $query->where('address', $keyword)->orWhere('url', 'like', '%' . addslashes($keyword) . '%');
        });
        $chain && $query = $query->where('chain', $chain);
        $list = $query->orderBy('chain')->orderBy('priority')->orderBy('id')->paginate()->toArray();
        foreach ($list['data'] as &$item) {
            $item['chain'] = Chain::from($item['chain']);
        }
        unset($item);
        return view('governance.rpc.index', [
            'active_menu' => 'governance.rpc',
            'title' => 'rpc list',
            'list' => $list,
            'keyword' => $keyword,
            'status' => $status,
            'status_dict' => ChainRpc::$status_dict,
            'bool_dict' => ChainRpc::$heartbeat_dict,
            'chain' => $chain,
        ]);
    }


    public function gasPrice(Request $request)
    {
        $url = $request->input('url');
        $provider = new HttpProvider($url, 20);
        $eth = new Eth($provider);
        $gasPrice = TransactionService::instance()->gasPrice($eth);
        $gasPrice = $gasPrice->toString();
        $current_price = bcdiv($gasPrice, bcpow(10, 9), 2);
        return $this->success($current_price);
    }

    public function edit(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            $data = (new ChainRpc)->where('id', $id)->first();
            return view('governance.rpc.edit', [
                'active_menu' => 'governance.rpc',
                'title' => 'edit rpc',
                'data' => $data,
                'id' => $id,
                'status_dict' => ChainRpc::$status_dict,
                'bool_dict' => ChainRpc::$heartbeat_dict,
            ]);
        } else {
            return view('governance.rpc.add', [
                'active_menu' => 'governance.rpc',
                'title' => 'add rpc',
                'status_dict' => ChainRpc::$status_dict,
                'bool_dict' => ChainRpc::$heartbeat_dict,
            ]);
        }
    }

    public function setOrder(Request $request)
    {
        $id = $request->input('id');
        $order = $request->input('order');
        $ret = (new ChainRpc)
            ->where('id', $id)->update([
                'priority' => $order,
            ]);
        return $ret ? $this->success() : $this->error(1, 'fail');
    }

    public function updateData(Request $request)
    {
        $id = $request->input('id');
        $chain = Chain::tryFrom($request->input('chain'));
        $url = $request->input('url');
        $status = $request->input('status');
        $heartbeat = $request->input('heartbeat');
        $priority = $request->input('priority');
        $data = [
            'chain' => $chain,
            'url' => $url,
            'status' => $status,
            'heartbeat' => $heartbeat,
            'priority' => $priority,
        ];
        if (!$chain) {
            return $this->error(1, 'chain select first');
        }
        if (!$url) {
            return $this->error(1, 'url select first');
        }
        try {
            $provider = new HttpProvider($url, 20);
            $chainId = TransactionService::instance()->getChainId($provider);
            $chainId = base_convert($chainId, 16, 10);
            if ($chain->chainId() != $chainId) {
                return $this->error(1, 'chainId not matched ' . $chain->chainId() . ' vs ' . $chainId);
            }
        } catch (\Exception $err) {
            return $this->error(1, 'url is not accessible msg:' . $err->getMessage());
        }
        if ($id) {
            $rpc = (new ChainRpc)
                ->where('chain', $chain)
                ->where('id', '!=', $id)
                ->where('url', $url)
                ->first();
            if ($rpc) {
                return $this->error(1, 'rpc exists');
            }
            $ret = (new ChainRpc)
                ->where('id', $id)
                ->update($data);
        } else {
            $account = (new ChainRpc)
                ->where('chain', $chain)
                ->where('url', $url)
                ->first();
            if ($account) {
                return $this->error(1, 'rpc exists');
            }
            $data['resp_time'] = 0;
            $data['block_number'] = 0;
            $ret = (new ChainRpc)->insert($data);
        }
        return $ret ? $this->success() : $this->error(1, 'fail');
    }

}
