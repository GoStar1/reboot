<?php

namespace App\Console\Commands\Scripts;

use App\Models\Admin\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Web3\Contract;
use Web3\Providers\HttpProvider;

class Test extends Command implements SignalableCommandInterface
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function handle(): void
    {
        $json = '[{"name":"createOrder","type":"function","inputs":[{"components":[{"internalType":"address","name":"makerAsset","type":"address"},{"internalType":"address","name":"takerAsset","type":"address"},{"internalType":"address","name":"maker","type":"address"},{"internalType":"address","name":"receiver","type":"address"},{"internalType":"uint256","name":"makingAmount","type":"uint256"},{"internalType":"uint256","name":"exchangeRate","type":"uint256"}],"internalType":"struct OrderMatching.MarkerOrder","name":"markerOrder","type":"tuple"}],"outputs":[],"stateMutability":"payable"}]';
        $provider = new HttpProvider('http://localhost', 20);
        $contract = new Contract($provider, $json);
        $contract = $contract->at('0x375f40ade6efdeea88b241bbf07949a30906eac7');
        $data = call_user_func_array([$contract, 'getData'], $params);
        $data = $contract->getData('createOrder',
            array_values([
                'makerAsset' => '0x375f40ade6efdeea88b241bbf07949a30906eac7',
                'takerAsset' => '0x375f40ade6efdeea88b241bbf07949a30906eac7',
                'maker' => '0x375f40ade6efdeea88b241bbf07949a30906eac7',
                'receiver' => '0x375f40ade6efdeea88b241bbf07949a30906eac7',
                'makingAmount' => '111',
                'exchangeRate' => '111',
            ]));
        var_dump($data);
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $question = "\nAre you sure you want to quit?(y/n)";
        if ($signal == 2 && $this->ask($question) === 'y') {
            exit;
        }
    }
}
