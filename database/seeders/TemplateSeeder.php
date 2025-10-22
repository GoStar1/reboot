<?php

namespace Database\Seeders;

use App\Enums\Chain;
use App\Models\Robot\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
//        $this->local();
        $this->goerli();
    }

    public function local(): void
    {
        $abi = file_get_contents(database_path('abis/cds.json'));
        (new Template)->forceFill([
            'chain' => Chain::Local,
            'contract' => '0x014ff1B1211D099B7Ab180F4E72fe9dC14483771',
            'name' => 'GYROX',
            'abi' => json_decode($abi),
        ])->save();

        $abi = file_get_contents(database_path('abis/usdt.json'));
        (new Template)->forceFill([
            'chain' => Chain::Local,
            'contract' => '0xf329d7515B2545314F98264F1720433D7a565d73',
            'name' => 'USDT',
            'abi' => json_decode($abi),
        ])->save();
    }


    public function goerli(): void
    {
        $abi = file_get_contents(database_path('abis/cds.json'));
        (new Template)->forceFill([
            'chain' => Chain::Goerli,
            'contract' => '0xe311f946473992B745d5a90fFc9bA7761104A487',
            'name' => 'GYROX',
            'abi' => json_decode($abi),
        ])->save();

        $abi = file_get_contents(database_path('abis/usdt.json'));
        (new Template)->forceFill([
            'chain' => Chain::Goerli,
            'contract' => '0xEb43e05116a69DCc60D32b78Afd01E44baEE2E6f',
            'name' => 'USDT',
            'abi' => json_decode($abi),
        ])->save();


        $abi = file_get_contents(database_path('abis/CDSLiquidity.json'));
        (new Template)->forceFill([
            'chain' => Chain::Goerli,
            'contract' => '0x1749dDEc4f0f214B9bAfE4EdCF9980a7233b8b83',
            'name' => 'liquidity',
            'abi' => json_decode($abi),
        ])->save();

        $abi = file_get_contents(database_path('abis/challenge.json'));
        (new Template)->forceFill([
            'chain' => Chain::Goerli,
            'contract' => '0x07E9c542eb5d1aB631AdA1330031224dcd1549de',
            'name' => 'challenge',
            'abi' => json_decode($abi),
        ])->save();
    }
}
