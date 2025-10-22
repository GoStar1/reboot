<?php

namespace App\Console\Commands\Scripts;

use App\Enums\Chain;
use App\Models\BlockChain\Account;
use App\Models\BlockChain\ChainRpc;
use App\Models\Robot\Template;
use App\Services\SystemUtils;
use App\Services\TransactionService;
use Illuminate\Console\Command;
use Web3\Contract;
use Web3\Providers\HttpProvider;

class SendToken extends Command
{
    protected $name = 'send-token';


    public function handle(): void
    {
        $this->mutiSendEth();
    }

    protected function mutiSendErc20(): void
    {
        $_private_key = '5a7ba5d9b8aa63e307cfb13eebb9b6820a2024a48f854385e550334f772302e7';
        $template = Template::find(4);
        $chain = Chain::Goerli;
        $amount = bcmul(100, bcpow(10, 18));
        $from = SystemUtils::privateToAddress($_private_key, $chain);
        $accounts = Account::where('chain', $chain)
            ->get(['address'])
            ->pluck('address')
            ->toArray();
        $method = 'transfer';
        $rpc = (new ChainRpc())->getProviderUrl($chain);
        $provider = new HttpProvider($rpc, 20);
        $transactionService = TransactionService::instance();
        foreach ($accounts as $account) {
            $args = [
                'recipient' => $account,
                'amount' => $amount,
            ];
            $contract = new Contract($provider, $template['abi']);
            $contract = $contract->at($template->contract);
            $params = array_merge([$method], array_values($args));
            $ret = $transactionService->sendTransaction($contract, $_private_key, $params);
            var_dump($ret);
        }
    }


    protected function mutiSendEth(): void
    {
        $private_key = '0x211e5619f41a9d1d95e40a60f1e0a96eb88f84fa00bbb694e2337c308e39d6fa';
        $chain = Chain::Local;
        $amount = bcmul('0.02', bcpow(10, 18), 0);
        $amount = '0x' . base_convert($amount, 10, 16);
        $accounts = Account::where('chain', $chain)
            ->get();
        $transactionService = TransactionService::instance();
        $rpc = (new ChainRpc())->getProviderUrl($chain);
        $provider = new HttpProvider($rpc, 20);
        foreach ($accounts as $account) {
            $ret = $transactionService->sendTransactionWithData($provider, $private_key, $account->address, $amount, '0x');
            var_dump($ret);
        }
    }

}
