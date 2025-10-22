<?php

namespace App\Console\Commands\BlockChain;


use App\Models\Robot\Withdraw;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class WithdrawCreate extends Command
{
    protected $name = 'withdraw:create';

    protected $description = 'withdraw create';

    public function handle(): void
    {
        $type = $this->option('type');
        if ($type === 'gate') {
            $this->gate();
        } else {
            $this->binance();
        }

    }

    private function binance(): void
    {
        $secret = $this->option('secret');
        $key = $this->option('key');
        $order_prefix = $this->option('order_prefix');
        $currency = $this->option('currency');
        $chain = $this->option('chain');
        $host = "https://api.binance.com";
        $url = '/sapi/v1/capital/withdraw/apply';
        $order_id_prefix = 'order_' . $order_prefix;
        $addresses = $this->option('addresses');
        $addresses = explode(',', $addresses);
        $amount = $this->option('amount');
        $req_time = time();
        foreach ($addresses as $i => $address) {
            $body = [
                "withdrawOrderId" => $order_id_prefix . '_' . $i,
                'network' => $chain,
                'walletType' => 0,
                'coin' => $currency,
                'address' => $address,
                'amount' => $amount,
            ];
            $req_time = $req_time + rand(10, 30) * 60;
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'X-MBX-APIKEY' => $key];
            (new Withdraw())->forceFill([
                'type' => Withdraw::TYPE_BINANCE,
                'url' => $host . $url,
                'method' => 'POST',
                'headers' => $headers,
                'extra' => [
                    'secret' => $secret,
                ],
                'req' => $body,
                'response' => [],
                'status' => Withdraw::STATUS_WAIT,
                'req_time' => $req_time,
                'response_time' => 0,
            ])->save();
        }
    }


    private function gate(): void
    {
        $secret = $this->option('secret');
        $key = $this->option('key');
        $order_prefix = $this->option('order_prefix');
        $currency = $this->option('currency');
        $chain = $this->option('chain');


        $host = "https://api.gateio.ws";
        $prefix = "/api/v4";
        $url = '/withdrawals';
        $order_id_prefix = 'order_' . $order_prefix;
        $addresses = $this->option('addresses');
        $addresses = explode(',', $addresses);
        $amount = $this->option('amount');
        $req_time = time();
        foreach ($addresses as $i => $address) {
            $body = [
                "withdraw_order_id" => $order_id_prefix . '_' . $i,
                "currency" => $currency,
                "address" => $address,
                "amount" => $amount,
                "memo" => "",
                "chain" => $chain,
            ];
            $req_time = $req_time + rand(10, 30) * 60;
            $headers = ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'KEY' => $key, 'Timestamp' => '', 'SIGN' => ''];
            (new Withdraw())->forceFill([
                'type' => Withdraw::TYPE_GATE,
                'url' => $host . $prefix . $url,
                'method' => 'POST',
                'headers' => $headers,
                'extra' => [
                    'key' => $key,
                    'secret' => $secret,
                    'path' => $prefix . $url,
                ],
                'req' => $body,
                'response' => [],
                'status' => Withdraw::STATUS_WAIT,
                'req_time' => $req_time,
                'response_time' => 0,
            ])->save();
        }
    }


    protected function getOptions(): array
    {
        return [
            ['secret', null, InputOption::VALUE_REQUIRED, 'secret'],
            ['key', null, InputOption::VALUE_REQUIRED, 'key'],
            ['order_prefix', null, InputOption::VALUE_REQUIRED, 'key'],
            ['type', null, InputOption::VALUE_REQUIRED, 'type'],
            ['chain', null, InputOption::VALUE_REQUIRED, 'chain'],
            ['currency', null, InputOption::VALUE_REQUIRED, 'currency'],
            ['amount', null, InputOption::VALUE_REQUIRED, 'amount'],
            ['addresses', null, InputOption::VALUE_REQUIRED, 'addresses'],
        ];
    }

}
