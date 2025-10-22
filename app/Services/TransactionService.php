<?php

namespace App\Services;

use App\Enums\Chain;
use App\Exceptions\ShowMsgException;
use App\Models\BlockChain\ChainRpc;
use Arr;
use Log;
use Web3\Contract;
use Web3\Eth;
use Web3\Methods\EthMethod;
use Web3\Net;
use Web3\Providers\HttpProvider;
use Web3\Utils;
use Web3p\EthereumTx\EIP1559Transaction;
use Web3p\EthereumUtil\Util;


class TransactionService extends Services
{
    private int $timeout = 20;

    function confirmTx($eth, $txHash)
    {
        $transaction = null;
        $l = 0;
        while (!$transaction) {
            if ($l++ > 600) {
                echo 'failed';
                return null;
            }
            $transaction = $this->getTransactionReceipt($eth, $txHash);
            if ($transaction) {
                return $transaction;
            } else {
                echo "Sleep one second and wait transaction to be confirmed" . PHP_EOL;
                sleep(1);
            }
        }
        return null;
    }

    function getChainId($provider)
    {
        $version = null;
        $provider->send(new EthMethod('eth_chainId', []), function ($err, $ver) use (&$version) {
            if ($err !== null) {
                throw $err;
            }
            $version = $ver;
        });
        return $version;
    }

    function estimateGas($provider, $transaction): string
    {
        $transaction = Arr::only($transaction, ['from', 'to', 'value', 'data', 'gasPrice', 'gas']);
        $gas = null;
        $provider->send(new EthMethod('eth_estimateGas', [$transaction]), function ($err, $ret) use (&$gas) {
            if ($err !== null) {
                throw $err;
            }
            $gas = base_convert($ret, 16, 10);
        });
        return $gas;
    }

    function getTransactionReceipt($eth, $txHash)
    {
        $tx = null;
        $eth->getTransactionReceipt($txHash, function ($err, $transaction) use (&$tx) {
            if ($err !== null) {
                throw $err;
            }
            $tx = $transaction;
        });
        return $tx;
    }

    function getTransaction($eth, $txHash)
    {
        $tx = null;
        $eth->getTransactionByHash($txHash, function ($err, $transaction) use (&$tx) {
            if ($err !== null) {
                throw $err;
            }
            $tx = json_decode(json_encode($transaction), true);
        });
        return $tx;
    }

    function getNonce($eth, $account, $status = 'latest')
    {
        $nonce = 0;
        $eth->getTransactionCount($account, $status, function ($err, $count) use (&$nonce) {
            if ($err !== null) {
                throw $err;
            }
            $nonce = $count;
        });
        return $nonce;
    }

    function getBlock($eth, $status = 'latest'): array
    {
        $block = [];
        $eth->getBlockByNumber($status, false, function ($err, $result) use (&$block) {
            if ($err !== null) {
                throw $err;
            }
            $block = json_decode(json_encode($result), true);
        });
        return $block;
    }


    function getBalance($eth, $address, $status = 'latest')
    {
        $balance = null;
        $eth->getBalance($address, $status, function ($err, $result) use (&$balance) {
            if ($err !== null) {
                throw $err;
            }
            $balance = $result->toString();
        });
        return $balance;
    }

    function gasPrice($eth)
    {
        $price = '';
        $eth->gasPrice(function ($err, $result) use (&$price) {
            if ($err !== null) {
                throw $err;
            }
            $price = $result;
        });
        return $price;
    }

    function toDecimal($num): string
    {
        return base_convert($num, 16, 10);
    }

    function toHex($num): string
    {
        return '0x' . base_convert($num, 10, 16);
    }

    function getFeeData($eth): array
    {
        $gasPrice = $this->gasPrice($eth);
        $gasPrice = $gasPrice->toString();
        $block = $this->getBlock($eth);
        $lastBaseFeePerGas = $block['baseFeePerGas'];
//        $maxPriorityFeePerGas = "1500000000";
        $maxPriorityFeePerGas = "15000";
        $maxFeePerGas = bcadd(bcmul($this->toDecimal($block['baseFeePerGas']), 2), $maxPriorityFeePerGas);
        return ['lastBaseFeePerGas' => $this->toDecimal($lastBaseFeePerGas), 'maxFeePerGas' => $maxFeePerGas, 'maxPriorityFeePerGas' => $maxPriorityFeePerGas, 'gasPrice' => $gasPrice];
    }

    public function decodeEvent($object, $signature_map, $ethAbi): array
    {
        $topic0 = $object->topics[0];
        if (!isset($signature_map[$topic0])) {
            return [];
        }
        $event = $signature_map[$topic0];
        $eventParameterNames = [];
        $eventParameterTypes = [];
        $eventIndexedParameterNames = [];
        $eventIndexedParameterTypes = [];
        $map_tuple = [];
        foreach ($event['inputs'] as $input) {
            if ($input['indexed']) {
                $eventIndexedParameterNames[] = $input['name'];
                $eventIndexedParameterTypes[] = $input['type'];
            } else {
                $eventParameterNames[] = $input['name'];
                if ($input['type'] == 'tuple') {
                    $types = [];
                    $map_tuple[$input['name']] = [];
                    foreach ($input['components'] as $component) {
                        $types[] = $component['type'];
                        $map_tuple[$input['name']][] = [$component['name'], $component['type']];
                    }
                    $eventParameterTypes[] = 'tuple(' . implode(',', $types) . ')';
                }
            }
        }
        $numEventIndexedParameterNames = count($eventIndexedParameterNames);
        $decodedData = array_combine($eventParameterNames, $ethAbi->decodeParameters($eventParameterTypes, $object->data));
        //decode the indexed parameter data
        for ($i = 0; $i < $numEventIndexedParameterNames; $i++) {
            //topics[0] is the event signature, so we start from $i + 1 for the indexed parameter data
            $decodedData[$eventIndexedParameterNames[$i]] = $ethAbi->decodeParameters([$eventIndexedParameterTypes[$i]], $object->topics[$i + 1])[0];
        }
        foreach ($event['inputs'] as $input) {
            $type = $input['type'];
            $val = $decodedData[$input['name']];
            if ($input['type'] == 'tuple') {
                if (isset($map_tuple[$input['name']])) {
                    $tuple_arr = $map_tuple[$input['name']];
                    $newVal = [];
                    foreach ($decodedData[$input['name']] as $_key => $_val_item) {
                        list($_name, $_type) = $tuple_arr[$_key];
                        $newVal[$_name] = $this->objToString($_type, $_val_item);
                    }
                    $decodedData[$input['name']] = $newVal;
                }
            } else {
                $newVal = $this->objToString($type, $val);
                $decodedData[$input['name']] = $newVal;
            }
        }
        //include block metadata for context, along with event data
        return [
            'block_number' => hexdec($object->blockNumber),
            'log_index' => base_convert($object->logIndex, 16, 10),
            'trans_hash' => $object->transactionHash,
            'contract_address' => Utils::toChecksumAddress($object->address),
            'name' => $event['name'],
            'args' => $decodedData,
        ];
    }

    protected function objToString($type, $val)
    {
        $newVal = $val;
        if (preg_match('/^uint\d*$/', $type)) {
            $newVal = $val->toString();
        } else if (preg_match('/^uint\d*\[\d*]$/', $type)) {
            $newVal = [];
            foreach ($val as $_key => $item) {
                $newVal[$_key] = $item->toString();
            }
        } else if ($type === 'address') {
            $newVal = Utils::toChecksumAddress($val);
        }
        return $newVal;
    }


    /**
     * @param Contract $contract
     * @param $nonce
     * @param $private_key
     * @param $params
     * @return mixed
     * @throws ShowMsgException
     */
    public function sendTransaction(Contract $contract, $nonce, $private_key, $params): mixed
    {
        $provider = $contract->getProvider();
        $util = new Util();
        $eth = $contract->getEth();
        $chainId = $this->getChainId($provider);
        $from = $util->publicKeyToAddress($util->privateKeyToPublicKey($private_key));
        $data = call_user_func_array([$contract, 'getData'], $params);
        $fee_data = $this->getFeeData($eth);
        $transaction_param = [
            'nonce' => '0x' . base_convert($nonce, 10, 16),
            'from' => $from,
            'to' => $contract->getToAddress(),
            'value' => '0x0',
//                'gasPrice' => '0x' . Utils::toWei('5', 'gwei')->toHex(),
//                'gasPrice' => $this->toHex($fee_data['gasPrice']),
            'maxPriorityFeePerGas' => $this->toHex($fee_data['maxPriorityFeePerGas']),
            'maxFeePerGas' => $this->toHex($fee_data['maxFeePerGas']),
            'data' => '0x' . $data,
            'chainId' => $chainId, // required
            'accessList' => [],
        ];
        $estimateGas = null;
        $params = array_merge($params, [
            $transaction_param,
            function ($err, $data) use (&$estimateGas) {
                if ($err !== null) {
                    echo $err->getMessage();
                    Log::error('message:' . $err->getMessage() . ' ' . $err);
                    throw new ShowMsgException($err->getMessage());
                }
                $estimateGas = $data->toString();
            }
        ]);
        call_user_func_array([$contract, 'estimateGas'], $params);
        $transaction_param['gasLimit'] = '0x' . base_convert(bcmul($estimateGas, '1.2'), 10, 16);
        $transaction = new EIP1559Transaction($transaction_param);
        $transaction->sign($private_key);
        $txHash = null;
        $eth->sendRawTransaction('0x' . $transaction->serialize(), function ($err, $tx) use ($eth, $from, &$txHash) {
            if ($err !== null) {
                throw new ShowMsgException($err->getMessage());
            }
            echo 'tx hash: ' . $tx . PHP_EOL;
            $txHash = $tx;
        });
        return $txHash;
    }


    /**
     * @param $provider
     * @param $private_key
     * @param $to
     * @param $amount
     * @param $nonce
     * @param $data
     * @return mixed
     * @throws ShowMsgException
     */
    public function sendTransactionWithData($provider, $private_key, $to, $amount, $nonce, $data): mixed
    {
        $util = new Util();
        $eth = new Eth($provider);
        $chainId = $this->getChainId($provider);
        $from = $util->publicKeyToAddress($util->privateKeyToPublicKey($private_key));
        $fee_data = $this->getFeeData($eth);
        $transaction_param = [
            'nonce' => '0x' . base_convert($nonce, 10, 16),
            'from' => $from,
            'to' => $to,
            'value' => $amount,
//                'gasPrice' => '0x' . Utils::toWei('5', 'gwei')->toHex(),
//                'gasPrice' => $this->toHex($fee_data['gasPrice']),
            'maxPriorityFeePerGas' => $this->toHex($fee_data['maxPriorityFeePerGas']),
            'maxFeePerGas' => $this->toHex($fee_data['maxFeePerGas']),
            'data' => $data,
            'chainId' => $chainId, // required
            'accessList' => [],
        ];
        $estimateGas = $this->estimateGas($provider, $transaction_param);
        $transaction_param['gasLimit'] = '0x' . base_convert(bcmul($estimateGas, '1.2'), 10, 16);
        $transaction = new EIP1559Transaction($transaction_param);
        $transaction->sign($private_key);
        $txHash = null;
        $eth->sendRawTransaction('0x' . $transaction->serialize(), function ($err, $tx) use ($eth, $from, &$txHash) {
            if ($err !== null) {
                throw new ShowMsgException($err->getMessage());
            }
            echo 'tx hash: ' . $tx . PHP_EOL;
            $txHash = $tx;
        });
        return $txHash;
    }


    /**
     * @param $url
     * @return int
     */
    public function lastBlockByUrl($url): int
    {
        $net = new Net(new HttpProvider($url, $this->timeout));
        $number = 0;
        $net->getProvider()->send(new EthMethod('eth_blockNumber', []), function ($err, $data) use (&$number) {
            if ($err !== null) {
                echo $err->getMessage();
                throw new ShowMsgException($err->getMessage(), $err->getCode());
            }
            $number = base_convert($data, 16, 10);
        });
        return $number;
    }


    /**
     * @param Chain $chain
     * @param $abi
     * @param $contractAddress
     * @param array $args
     * @return mixed
     * @throws ShowMsgException
     */
    public function getResult(Chain $chain, $abi, $contractAddress, array $args = []): mixed
    {
        $i = 0;
        $return_data = null;
        $url = null;
        do {
            try {
                $url = (new ChainRpc())->getProviderUrl($chain);
                if (!$url) {
                    throw new ShowMsgException('not found url');
                }
                $provider = new HttpProvider($url, $this->timeout);
                $contract = new Contract($provider, $abi);
                $contract->at($contractAddress);
                if ($i === 0) {
                    $functions = $contract->getFunctions();
                    $function = null;
                    foreach ($functions as $_function) {
                        if ($_function["name"] === $args[0]) {
                            $function = $_function;
                            break;
                        }
                    }
                    $args = array_merge($args, [
                        function ($err, $data) use (&$return_data, $function) {
                            if ($err !== null) {
                                throw new ShowMsgException($err->getMessage(), $err->getCode());
                            }
                            foreach ($function['outputs'] as $i => $param) {
                                if ($param['type'] === 'address') {
                                    if ($param['name']) {
                                        $data[$param['name']] = Utils::toChecksumAddress($data[$param['name']]);
                                    } else {
                                        $data[$i] = Utils::toChecksumAddress($data[$i]);
                                    }
                                } else if (preg_match('/^uint\d*$/', $param['type'])) {
                                    if ($param['name']) {
                                        $data[$param['name']] = $data[$param['name']]->toString();
                                    } else {
                                        $data[$i] = $data[$i]->toString();
                                    }
                                } else if (preg_match('/^uint\d*\[\d*]$/', $param['type'])) {
                                    if ($param['name']) {
                                        $val = $data[$param['name']];
                                    } else {
                                        $val = $data[$i];
                                    }
                                    $newVal = [];
                                    foreach ($val as $_key => $item) {
                                        $newVal[$_key] = $item->toString();
                                    }
                                    if ($param['name']) {
                                        $data[$param['name']] = $newVal;
                                    } else {
                                        $data[$i] = $newVal;
                                    }
                                }
                            }
                            $return_data = $data;
                        },
                    ]);
                }
                call_user_func_array([$contract, 'call'], $args);
                if ($return_data !== null) {
                    break;
                }
            } catch (\Exception $e) {
                echo "[call contract error][msg:{$e->getMessage()}]\n";
                Log::notice("[call contract error][chain:{$chain->name}][rpc:$url][msg:{$e->getMessage()}]");
                sleep(1);
            }
        } while ($i++ < 3);
        if ($return_data === null) {
            throw new ShowMsgException('not fund result');
        }
        return $return_data;
    }
}
