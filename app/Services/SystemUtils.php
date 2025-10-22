<?php


namespace App\Services;


use App\Enums\Chain;
use Elliptic\EC;
use Elliptic\EC\KeyPair;
use kornrunner\Keccak;

class SystemUtils extends Services
{

    public static function isNull($var)
    {
        return $var === '' || $var === null;
    }

    public static function isEthAddr($addr)
    {
        return !!preg_match('/^0x[0-9a-fA-F]{40}$/', $addr);
    }

    public static function isProduction(): bool
    {
        return \App::environment('production');
    }

    public static function is_ip_in_china($ip): bool
    {
        $ip = trim($ip);
        $first_a = explode(".", $ip);
        if (!isset($first_a[0]) || $first_a[0] == "") {
            return false;
        }
        $first = $first_a[0];
        $arr_range = self::hash_get('china_ip_hash', $first);
        if (!is_array($arr_range) || sizeof($arr_range) == 0) {
            return false;
        }
        if (self::is_ip_in_arr_range($ip, $arr_range) == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $privateKey
     * @param Chain $chain
     * @return false|string
     * @throws \Exception
     */
    public static function privateToAddress(string $privateKey, Chain $_chain): string
    {
        $ellipticCurve = new EC('secp256k1');
        $keyPair = new KeyPair($ellipticCurve, [
            'priv' => $privateKey,
            'privEnc' => 'hex'
        ]);
        $publicKey = $keyPair->getPublic('hex');
        $publicKey = substr($publicKey, 2, 130);
        $hash = Keccak::hash(hex2bin($publicKey), 256);
        return '0x' . substr($hash, -40);
    }

    //判断一个ip是否属于ip的range数组
    protected static function is_ip_in_arr_range($ip, $arr_range): bool
    {
        $ip_long = (double)(sprintf("%u", ip2long($ip)));
        foreach ($arr_range as $k => $one) {
            $one = trim($one);
            $arr_one = explode("--", $one);
            if (!isset($arr_one[0]) || !isset($arr_one[1])) {
                continue;
            }
            $begin = $arr_one[0];
            $end = $arr_one[1];
            if ($ip_long >= $begin && $ip_long <= $end) {
                return true;
            }
        }
        return false;
    }

//得到一个hash中对应key的value
    protected static function hash_get($hash_name, $key_name)
    {
        $str = \Predis::hget($hash_name, $key_name);
        $arr = json_decode($str, true);
        return $arr;
    }


    private function replace($value): array|string
    {
        $ret = str_replace('+', '-', $value);
        $ret = str_replace('/', '_', $ret);
        return str_replace('=', '', $ret);
    }

    /**
     * @throws \Exception
     */
    public function encryptPrivateKey($message): string
    {
        $cipher = 'AES-256-CBC';
        $iv = random_bytes(openssl_cipher_iv_length($cipher) / 2);
        $iv = bin2hex($iv);
        $key = hash('sha256', config('blockchain.key'), true);
        $_value = json_encode([
            'message' => $message,
        ]);
        // First we will encrypt the value using OpenSSL. After this is encrypted we
        // will proceed to calculating a MAC for the encrypted value so that this
        // value can be verified later as not having been changed by the users.
        $value = \openssl_encrypt(
            $_value,
            $cipher, $key, 0, $iv
        );
        $result = $this->replace($value) . '.' . $this->replace(base64_encode($iv));
        $hash = hash_hmac('sha256', $result, $key, true);
        return $result . '.' . $this->replace(base64_encode($hash));
    }

    public static function secondsDiff($seconds): string
    {
        if ($seconds > 86400) {
            return bcdiv($seconds, 86400, 3) . ' days';
        }
        if ($seconds > 3600) {
            return bcdiv($seconds, 3600, 3) . ' hours';
        }
        if ($seconds > 60) {
            return bcdiv($seconds, 60, 3) . ' minutes';
        }
        return $seconds . ' seconds';
    }

}

