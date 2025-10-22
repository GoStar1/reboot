<?php

namespace App\Console\Commands\BlockChain;

use App\Models\Robot\Withdraw;
use Illuminate\Console\Command;
use Exception;
use Log;
use Arr;

class WithdrawExecute extends Command
{
    protected $signature = 'withdraw:execute';

    protected $description = 'withdraw execute';

    public function handle(): void
    {
        $withdraws = Withdraw::where('status', Withdraw::STATUS_WAIT)
//            ->where('req_time', '<=', time())
            ->limit(10)
            ->get();
        foreach ($withdraws as $withdraw) {
            if ($withdraw->type == Withdraw::TYPE_GATE) {
                $this->gate($withdraw);
            } else {
                $this->binance($withdraw);
            }
            sleep(10);
        }
    }

    private function binance($withdraw): void
    {
        $extra = $withdraw->extra;
        $req = $withdraw->req;
        $req['timestamp'] = intval(microtime(true) * 1000);
        $query_str = http_build_query($req);
        $sign = hash_hmac('sha256', $query_str, $extra['secret']);
        $common_headers = $withdraw->headers;
        $headers = [];
        foreach ($common_headers as $k => $v) {
            $headers[] = "$k: $v";
        }
//        var_dump($withdraw->url . '?' . $query_str . '&signature=' . $sign, $headers);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $withdraw->url . '?' . $query_str . '&signature=' . $sign);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $_result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($_result, true);
            if ($result) {
                if (Arr::get($result, 'code') === 200) {
                    $withdraw->status = Withdraw::STATUS_SUCCESS;
                } else {
                    $withdraw->status = Withdraw::STATUS_FAILED;
                }
                var_dump($result);
                $withdraw->response = $result;
            } else {
                var_dump($_result);
                $withdraw->response = $_result;
            }
            $withdraw->save();
        } catch (Exception $e) {
            Log::warning('[withdrawExecute:' . $withdraw->id . '][binance][status ' . $e->getCode() . '][ret:' . $_result . '][' . json_encode($e->getMessage()) . ']');
        }
    }


    private function gate(Withdraw $withdraw): void
    {
        $extra = $withdraw->extra;
        $request_content = json_encode($withdraw->req);
        list($sign, $timestamp) = $this->getGateSign($withdraw->method, $extra['path'], $extra['secret'], "", $request_content);
        $common_headers = $withdraw->headers;
        $common_headers['Timestamp'] = $timestamp;
        $common_headers['SIGN'] = $sign;
        $headers = [];
        foreach ($common_headers as $k => $v) {
            $headers[] = "$k: $v";
        }
//        var_dump($withdraw->url, $headers, $request_content);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $withdraw->url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request_content);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $_result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $result = json_decode($_result, true);
            if ($result) {
                if ($code === 202) {
                    $withdraw->status = Withdraw::STATUS_SUCCESS;
                } else {
                    $withdraw->status = Withdraw::STATUS_FAILED;
                }
                var_dump($result);
                $withdraw->response = $result;
            } else {
                var_dump($_result);
                $withdraw->response = $_result;
            }
            $withdraw->save();
        } catch (Exception $e) {
            Log::warning('[WithdrawExecute:' . $withdraw->id . '][gate][status ' . $e->getCode() . '][ret:' . $_result . '][' . json_encode($e->getMessage()) . ']');
        }
    }


    private function getGateSign($method, $url, $secret, $query_string = null, $payload_string = null): array
    {
        $t = microtime(true);
        $content = $method . "\n" . $url . "\n" . $query_string . "\n" . hash('sha512', $payload_string) . "\n" . $t;
        return [hash_hmac('sha512', $content, $secret), $t];
    }
}
