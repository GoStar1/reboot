<?php

namespace Database\Seeders;

use App\Enums\Chain;
use App\Models\BlockChain\ChainRpc;
use Illuminate\Database\Seeder;

class ChainRpcSeeder extends Seeder
{
    public function run(): void
    {
        $this->local();
        $this->goerli();
    }

    public function local(): void
    {
        (new ChainRpc())->forceFill([
            'chain' => Chain::Local,
            'url' => 'http://127.0.0.1:7545',
            'status' => 1,
            'heartbeat' => 0,
            'block_number' => 0,
            'resp_time' => 0,
        ])->save();
    }

    public function goerli(): void
    {
        (new ChainRpc())->forceFill([
            'chain' => Chain::Goerli,
            'url' => 'https://arbitrum-goerli.infura.io/v3/efae3a02063a4818bc512ec1075cd8a2',
            'status' => 1,
            'heartbeat' => 0,
            'block_number' => 0,
            'resp_time' => 0,
        ])->save();
    }
}
