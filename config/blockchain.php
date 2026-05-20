<?php

return [


    'node_url'         => env('QUORUM_NODE_URL'),
    'chain_id'         => (int) env('QUORUM_CHAIN_ID', 1337),
    'contract_address' => env('QUORUM_CONTRACT_ADDRESS'),
    'from_address'     => env('QUORUM_FROM_ADDRESS'),
    'from_key'         => env('QUORUM_FROM_KEY'),

    /*Gas & transaction tuning*/

    'gas_limit'        => (int) env('QUORUM_GAS_LIMIT', 300000),
    'gas_price'        => (int) env('QUORUM_GAS_PRICE', 0),

    'explorer_tx_url'  => env('QUORUM_EXPLORER_TX_URL', ''),

    'quorum_enabled_events' => [
        'CRISIS_VERIFIED',
        'REPORT_REJECTED',
        'DEATH_CONFIRMED',
        'LDMS_TRIGGERED',
        // 'DONATION_RECORDED',  // later la
    ],

];
