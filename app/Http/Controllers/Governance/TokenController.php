<?php

namespace App\Http\Controllers\Governance;

use App\Enums\Chain;
use App\Http\Controllers\Controller;
use App\Models\BlockChain\Assets;
use App\Models\BlockChain\Token;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\Request;
use Str;

class TokenController extends Controller
{
    public function index(Request $request)
    {
        $chain = Chain::tryFrom($request->input('chain'));
        $keyword = $request->input('keyword');
        $query = new Token;
        if ($keyword) {
            if (Str::startsWith($keyword, '0x')) {
                $query = $query->where('address', $keyword);
            } else {
                $query = $query->where('name', 'like', '%' . addslashes($keyword) . '%');
            }
        }
        $chain && $query = $query->where('chain', $chain);
        $list = $query->orderBy('token_id')->paginate()->toArray();
        foreach ($list['data'] as &$item) {
            $item['chain'] = Chain::from($item['chain']);
            $item['total'] = Assets::where('token_id', $item['token_id'])->sum('balance');
        }
        unset($item);
        return view('governance.token.index', [
            'active_menu' => 'governance.token',
            'title' => 'Token List',
            'list' => $list,
            'keyword' => $keyword,
            'chain' => $chain,
        ]);
    }

    public function edit(Request $request)
    {
        $token_id = $request->input('token_id');
        if ($token_id) {
            $data = (new Token)->where('token_id', $token_id)->first();
        } else {
            $data = ['chain' => null];
        }
        return view('governance.token.edit', [
            'active_menu' => 'governance.token',
            'title' => $token_id ? 'Edit Token' : 'Add Token',
            'data' => $data,
            'token_id' => $token_id,
        ]);
    }

    public function updateData(Request $request)
    {
        $token_id = $request->input('token_id');
        $chain = Chain::tryFrom($request->input('chain'));
        $contract_address = $request->input('contract');
        $decimals = $request->input('decimals');
        $name = $request->input('name');
        if (!$chain) {
            return $this->error(1, 'input chain first');
        }
        if (!$name) {
            return $this->error(1, 'input name first');
        }
        if (!$contract_address) {
            return $this->error(1, 'input contract first');
        }
        if (!$decimals) {
            try {
                $abi = file_get_contents(database_path('abis/usdt.json'));
                list($decimals) = TransactionService::instance()->getResult($chain, $abi, $contract_address, [
                    'decimals',
                ]);
            } catch (Exception $e) {
                return $this->error(1, $e->getMessage());
            }
        }
        $data = [
            'chain' => $chain,
            'contract' => $contract_address,
            'decimals' => $decimals,
            'name' => $name,
        ];
        $query = (new Token)->where('chain', $chain)
            ->where('contract', $contract_address);
        $token_id && $query = $query->where('token_id', '!=', $token_id);
        if ($query->first()) {
            return $this->error(1, 'Token exists');
        }
        if ($token_id) {
            $ret = Token::find($token_id)->forceFill($data)->save();
        } else {
            $ret = (new Token)->forceFill($data)->save();
        }
        return $ret ? $this->success() : $this->error(1, 'fail2');
    }
}
