<?php

namespace Database\Seeders;

use App\Enums\Chain;
use App\Models\BlockChain\Account;
use App\Models\BlockChain\ChainRpc;
use App\Services\SystemUtils;
use App\Services\TransactionService;
use Crypt;
use Exception;
use FurqanSiddiqui\BIP39\BIP39;
use Illuminate\Database\Seeder;
use Jundayw\Bip44\Bip44HierarchicalKey;
use Web3\Eth;
use Web3\Providers\HttpProvider;

class BlockChainSeeder extends Seeder
{
    /**
     * @throws Exception
     */
    public function run(): void
    {
        $this->local();
        $this->goerli();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function local(): void
    {
        $seeds = 'around near miracle pioneer inner exhaust razor dragon fruit force hawk accuse';
        $HDKey = Bip44HierarchicalKey::fromEntropy(bin2hex(BIP39::words($seeds)->generateSeed()))->derive("44'/60'/0'/0");
        $data = [];
        $count = 10;
        $tags = 'init';
        $chain = Chain::Local;
        $now = gmdate('Y-m-d H:i:s');
        $transactionService = TransactionService::instance();
        for ($i = 0; $i < $count; $i++) {
            $hdChild = $HDKey->deriveChild($i);
            $privateKey = Crypt::encryptString(bin2hex($hdChild->getPrivateKey()));
            $address = SystemUtils::privateToAddress($hdChild->getPrivateKey(), $chain);
            $rpc = (new ChainRpc())->getProviderUrl($chain);
            $provider = new HttpProvider($rpc, 20);
            $eth = new Eth($provider);
            $nonce = $transactionService->getNonce($eth, $address);
            $data[] = [
                'address' => $address,
                'chain' => $chain,
                'private_key' => $privateKey,
                'tags' => $tags,
                'nonce' => $nonce->toString(),
                'balance' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($data, 50) as $arr) {
            Account::insert($arr);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public function goerli(): void
    {
        $HDKey = Bip44HierarchicalKey::fromEntropy(bin2hex(BIP39::Generate()->generateSeed()))->derive("44'/60'/0'/0");
        $data = [];
        $count = 10;
        $tags = 'init';
        $chain = Chain::Goerli;
        $now = gmdate('Y-m-d H:i:s');
        for ($i = 0; $i < $count; $i++) {
            $hdChild = $HDKey->deriveChild($i);
            $privateKey = Crypt::encryptString(bin2hex($hdChild->getPrivateKey()));
            $address = SystemUtils::privateToAddress($hdChild->getPrivateKey(), $chain);
            $data[] = [
                'address' => $address,
                'chain' => $chain,
                'private_key' => $privateKey,
                'tags' => $tags,
                'nonce' => 0,
                'balance' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($data, 50) as $arr) {
            Account::insert($arr);
        }
    }
}
