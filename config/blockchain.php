<?php

return [
    'node_url'         => env('QUORUM_NODE_URL'),
    'contract_address' => env('QUORUM_CONTRACT_ADDRESS'),
    'from_address'     => env('QUORUM_FROM_ADDRESS'),
    'chain_id'         => env('QUORUM_CHAIN_ID', 1337),
    'gas_limit'        => env('QUORUM_GAS_LIMIT', 200000),
];
