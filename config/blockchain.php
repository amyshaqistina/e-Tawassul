<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permissioned EVM chain settings
    |--------------------------------------------------------------------------
    |
    | The QUORUM_* prefix is used as a category label for "permissioned
    | Ethereum chain" — it does not imply the specific GoQuorum client.
    | This implementation runs against Hyperledger Besu with QBFT 2.0
    | consensus (ConsenSys's actively-maintained successor to GoQuorum).
    */

    'node_url'         => env('QUORUM_NODE_URL'),
    'chain_id'         => (int) env('QUORUM_CHAIN_ID', 1337),

    'contract_address' => env('QUORUM_CONTRACT_ADDRESS'),

    /*
    |--------------------------------------------------------------------------
    | Deployer / sender account
    |--------------------------------------------------------------------------
    |
    | The Ethereum account that submits hash-recording transactions.
    | The private key MUST be the 64-hex-char raw key (with or without 0x prefix).
    | Keep `from_key` out of source control — only set it in .env, never .env.example.
    */

    'from_address'     => env('QUORUM_FROM_ADDRESS'),
    'from_key'         => env('QUORUM_FROM_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Gas & transaction tuning
    |--------------------------------------------------------------------------
    */

    'gas_limit'        => (int) env('QUORUM_GAS_LIMIT', 300000),
    'gas_price'        => (int) env('QUORUM_GAS_PRICE', 0),

    /*
    |--------------------------------------------------------------------------
    | Block explorer URL pattern (optional)
    |--------------------------------------------------------------------------
    |
    | If you run a block explorer (Blockscout, etc.), set this to its URL
    | with {tx} placeholder. The admin UI uses it to link out to tx details.
    | Leave empty to suppress explorer links.
    */

    'explorer_tx_url'  => env('QUORUM_EXPLORER_TX_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Event types eligible for on-chain anchoring
    |--------------------------------------------------------------------------
    |
    | Events listed here will be submitted to the Quorum chain when the chain
    | is reachable. Events NOT listed always run in simulation mode (hash-only
    | row in the `blockchain` table), regardless of QUORUM_NODE_URL being set.
    |
    | DONATION_RECORDED is intentionally OMITTED for Phase 1 — the donation
    | flow is being reworked with a real payment provider. Once that's done,
    | add 'DONATION_RECORDED' to this list to anchor donations on-chain.
    */

    'quorum_enabled_events' => [
        'CRISIS_VERIFIED',
        'REPORT_REJECTED',
        'DEATH_CONFIRMED',
        'LDMS_TRIGGERED',
        // 'DONATION_RECORDED',  // Phase 2 — enable after payment integration
    ],

];
