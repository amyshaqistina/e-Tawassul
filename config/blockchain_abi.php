<?php

/*
|--------------------------------------------------------------------------
| CrisisAudit contract ABI
|--------------------------------------------------------------------------
|
| ABI = Application Binary Interface. Tells web3.php how to encode calls
| to the CrisisAudit smart contract — what each function takes as input,
| what it returns, and what events it emits.
|
| This is a *trimmed* ABI containing only the functions BlockchainService
| needs to call:
|   - recordEvent(string,bytes32)  → write
|   - entryCount()                 → read (sanity check)
|   - verifyHash(bytes32)          → read (admin's "verify a hash" form)
|
| Source: e-tawassul-contract/artifacts/contracts/CrisisAudit.sol/CrisisAudit.json
|
| Returned as a JSON-encoded string because web3.php's Contract constructor
| expects either a JSON string or a PHP array. Using a string here keeps it
| copy-pastable from the Hardhat artifact file.
*/

return [
    'crisis_audit' => json_encode([
        [
            'type'   => 'function',
            'name'   => 'recordEvent',
            'inputs' => [
                ['name' => 'eventType', 'type' => 'string'],
                ['name' => 'hash',      'type' => 'bytes32'],
            ],
            'outputs' => [
                ['name' => 'id', 'type' => 'uint256'],
            ],
            'stateMutability' => 'nonpayable',
        ],
        [
            'type'   => 'function',
            'name'   => 'entryCount',
            'inputs' => [],
            'outputs' => [
                ['name' => '', 'type' => 'uint256'],
            ],
            'stateMutability' => 'view',
        ],
        [
            'type'   => 'function',
            'name'   => 'verifyHash',
            'inputs' => [
                ['name' => 'hash', 'type' => 'bytes32'],
            ],
            'outputs' => [
                ['name' => 'exists', 'type' => 'bool'],
                ['name' => 'id',     'type' => 'uint256'],
            ],
            'stateMutability' => 'view',
        ],
        [
            'type'      => 'event',
            'name'      => 'EventRecorded',
            'anonymous' => false,
            'inputs'    => [
                ['name' => 'id',         'type' => 'uint256', 'indexed' => true],
                ['name' => 'hash',       'type' => 'bytes32', 'indexed' => true],
                ['name' => 'eventType',  'type' => 'string',  'indexed' => false],
                ['name' => 'recordedBy', 'type' => 'address', 'indexed' => true],
                ['name' => 'timestamp',  'type' => 'uint256', 'indexed' => false],
            ],
        ],
    ]),
];
