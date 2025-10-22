<?php

namespace App\Enums;

enum Chain: int
{

    case Ethereum = 6;
    case BscMain = 7;
    case KccMain = 8;

    case Avalanche = 11;

    case Map = 12;
    case ArbitrumOne = 9;

    case Sepolia = 10;

    case Local = 1;
    case GoerliARB = 2;
    case KccTest = 3;
    case BscTest = 4;
    case Goerli = 5;

    function explorer(): string
    {
        return match ($this) {
            Chain::Local => 'https://arbiscan.io',
            Chain::GoerliARB => 'https://goerli.arbiscan.io',
            Chain::KccTest => 'https://scan-testnet.kcc.network',
            Chain::BscTest => 'https://testnet.bscscan.com',
            Chain::Goerli => 'https://goerli.etherscan.io',
            Chain::Ethereum => 'https://etherscan.io',
            Chain::BscMain => 'https://bscscan.com',
            Chain::KccMain => 'https://scan.kcc.io',
            Chain::ArbitrumOne => 'https://arbiscan.io',
            Chain::Sepolia => 'https://sepolia.etherscan.io',
            Chain::Avalanche => 'https://snowtrace.io',
            Chain::Map =>'https://www.maposcan.io',
        };
    }

    function chainId(): int
    {
        return match ($this) {
            Chain::Local => 1337,
            Chain::GoerliARB => 421613,
            Chain::KccTest => 322,
            Chain::BscTest => 97,
            Chain::Goerli => 5,
            Chain::Ethereum => 1,
            Chain::BscMain => 56,
            Chain::KccMain => 321,
            Chain::ArbitrumOne => 42161,
            Chain::Sepolia => 11155111,
            Chain::Avalanche => 43114,
            Chain::Map => 22776,
        };
    }
}
