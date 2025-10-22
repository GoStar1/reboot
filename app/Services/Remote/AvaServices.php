<?php

namespace App\Services\Remote;

use App\Models\BlockChain\Assets;
use App\Services\Services;

class AvaServices extends Services
{

    public function getHeaders(): array
    {
        return [
            'Content-Type: application/json',
            'Accept: application/json, text/plain, */*',
            'Sec-Fetch-Site: same-origin',
            'Accept-Language: en-us',
            'Accept-Encoding: gzip, deflate, br',
            'Sec-Fetch-Mode: cors',
            'Host: avascriptions.com',
            'Origin: https://avascriptions.com',
            'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6.1 Safari/605.1.15',
            'Referer: https://avascriptions.com/token/search?address=0x536e1ba44d791364bb0b5673ab3968deebe77a0c',
            'Sec-Fetch-Dest: empty',
            'Cookie: _ga=GA1.1.1127220208.1703616590; _ga_ELFWPY3QK5=GS1.1.1703946141.2.1.1703946474.0.0.0; cf_clearance=QPCngu5.ruJZ738sl5z0j8A3z5.MHXJVz4OZxJZeAqk-1703946167-0-2-e2190ed9.79c584ce.b5185139-250.0.0',
        ];
    }

    public function subscriptionTokens($token, $address)
    {
        $url = 'https://avascriptions.com/api/asc20/tokens';
        $page = 1;
        $pageSize = 12;
        do {
            $ch = curl_init();
            $req = [];
            $req['address'] = $address;
            $req['page'] = $page;
            $req['pageSize'] = $pageSize;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $_result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($_result, true);
            if ($result['status'] == 200) {
                $count = $result['data']['count'];
                if ($count == 0) {
                    return 0;
                } else {
                    foreach ($result['data']['list'] as $item) {
                        if ($item['tick'] === $token) {
                            return $item['amount'];
                        }
                    }
                    if ($count <= $page * $pageSize) {
                        return null;
                    }
                    $page++;
                }
            }
        } while (true);
    }

    public function saveAsset($account_id, $token_id, $balance): void
    {
        $asset = Assets::where('account_id', $account_id)
            ->where('token_id', $token_id)->first();
        if ($asset) {
            $asset->balance = $balance;
        } else {
            $asset = new Assets();
            $asset->forceFill([
                'token_id' => $token_id,
                'balance' => $balance,
                'account_id' => $account_id,
            ]);
        }
        $asset->save();
    }


    public function saveAssetOp($account_id, $token_id, $isAdd, $amount): void
    {
        $asset = Assets::where('account_id', $account_id)
            ->where('token_id', $token_id)->first();
        if ($asset) {
            if ($isAdd) {
                $asset->balance = bcadd($amount, $asset->balance, 18);
            } else {
                $asset->balance = bcsub($asset->balance, $amount, 18);
            }
        } else {
            if ($isAdd) {
                $asset = new Assets();
                $asset->forceFill([
                    'token_id' => $token_id,
                    'balance' => $amount,
                    'account_id' => $account_id,
                ]);
            } else {
                return;
            }
        }
        $asset->save();
    }
}
