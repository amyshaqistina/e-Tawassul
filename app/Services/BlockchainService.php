<?php

namespace App\Services;

use App\Models\Blockchain;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use kornrunner\Keccak;
use phpseclib\Math\BigInteger;
use Web3\Contract;
use Web3\Utils;
use Web3p\EthereumTx\Transaction;
use Web3\Web3;


class BlockchainService
{

    protected ?Web3 $web3 = null;
    protected ?Contract $contract = null;

    // =================================================================
    //  Public API
    // =================================================================

    /**
     * Generate a deterministic SHA-256 hash of $data.
     *
     * Keys are recursively sorted before JSON-encoding so identical
     * payloads always produce the same hash regardless of input order.
     */
    public function generateHash(array $data): string
    {
        $canonical = $this->canonicalize($data);
        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return hash('sha256', $json);
    }

    /**
     * Record a hashed event.
     *
     * @param  string $eventType  e.g. CRISIS_VERIFIED
     * @param  array  $eventData  payload to hash (stays off-chain)
     * @param  int|string|null $referenceId
     * @param  string|null     $referenceTable
     * @return array  ['blockchain_id'=>int, 'hash'=>string, 'tx_hash'=>?string, 'mode'=>string]
     */
    public function recordEvent(
        string $eventType,
        array $eventData,
        int|string|null $referenceId = null,
        ?string $referenceTable = null
    ): array {
        $hash = $this->generateHash($eventData);
        $txHash = null;
        $mode = 'simulation';
        if ($this->isEventEligibleForChain($eventType) && $this->isChainConfigured()) {
            try {
                $txHash = $this->submitOnChain($eventType, $hash);
                if ($txHash) {
                    $mode = 'onchain';
                }
            } catch (\Throwable $e) {
                Log::warning('onchain submission failed, falling back to simulation', [
                    'event' => $eventType,
                    'hash'  => $hash,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $record = Blockchain::create([
            'data_from'       => $eventType,
            'data_type'       => 'event_hash',
            'stored_data'     => $hash,
            'hash_type'       => 'SHA-256',
            'verified'        => true,
            'timestamp'       => now(),
            'tx_hash'         => $txHash,
            'mode'            => $mode,
            'reference_table' => $referenceTable,
            'reference_id'    => is_numeric($referenceId) ? (int) $referenceId : null,
            'payload_meta'    => ['fields' => array_keys($eventData)],
        ]);

        return [
            'blockchain_id' => $record->blockchain_id,
            'hash'          => $hash,
            'tx_hash'       => $txHash,
            'mode'          => $mode,
        ];
    }

    /**
     * Re-compute the hash of $originalData and compare to a stored record.
     */
    public function verifyHash(int $blockchainId, array $originalData): bool
    {
        $record = Blockchain::find($blockchainId);
        if (!$record) {
            return false;
        }
        return hash_equals($record->stored_data, $this->generateHash($originalData));
    }

    /**
     * Look up a free-form hash string in the MySQL audit table.
     */
    public function verifyHashString(string $hash): ?Blockchain
    {
        return Blockchain::where('stored_data', $hash)->first();
    }

    /**
     * (Optional) Verify a hash exists ON-CHAIN as well, by calling the
     * contract's verifyHash(bytes32) view function. Returns
     * ['exists'=>bool, 'id'=>int] or null on chain error.
     */
    public function verifyHashOnChain(string $hash): ?array
    {
        if (!$this->isChainConfigured()) {
            return null;
        }
        try {
            $contract = $this->getContract();
            $hashBytes32 = '0x' . str_pad(ltrim($hash, '0x'), 64, '0', STR_PAD_LEFT);

            $result = null;
            $contract->call('verifyHash', $hashBytes32, function ($err, $data) use (&$result) {
                if ($err) {
                    throw new \RuntimeException($err->getMessage());
                }
                $result = $data;
            });

            if (!$result) {
                return null;
            }

            return [
                'exists' => (bool) ($result['exists'] ?? $result[0] ?? false),
                'id'     => (int) (
                    isset($result['id'])
                        ? $result['id']->toString()
                        : (isset($result[1]) ? $result[1]->toString() : 0)
                ),
            ];
        } catch (\Throwable $e) {
            Log::warning('verifyHashOnChain failed', [
                'hash'  => $hash,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Build a block-explorer URL for a tx hash, or null if not configured.
     */
    public function getTxExplorerUrl(?string $txHash): ?string
    {
        if (!$txHash) {
            return null;
        }
        $pattern = (string) config('blockchain.explorer_tx_url', '');
        if ($pattern === '') {
            return null;
        }
        return str_replace('{tx}', $txHash, $pattern);
    }

    public function getAuditLog(): Collection
    {
        return Blockchain::orderByDesc('timestamp')->limit(500)->get();
    }

    //  Internal — chain integration

    /*Submit a recordEvent transaction to the chain and return its tx hash.*/

    protected function submitOnChain(string $eventType, string $hashHex): ?string
    {
        $contractAddress = config('blockchain.contract_address');
        $fromAddress     = config('blockchain.from_address');
        $fromKey         = config('blockchain.from_key');
        $chainId         = (int) config('blockchain.chain_id');
        $gasLimit        = (int) config('blockchain.gas_limit', 300000);
        $gasPrice        = (int) config('blockchain.gas_price', 0);

        if (!$contractAddress || !$fromAddress || !$fromKey) {
            throw new \RuntimeException('QUORUM_CONTRACT_ADDRESS, QUORUM_FROM_ADDRESS and QUORUM_FROM_KEY must all be set in .env');
        }

        // Normalize 0x prefix on the hash
        $hashBytes32 = '0x' . str_pad(ltrim($hashHex, '0x'), 64, '0', STR_PAD_LEFT);

        // 1. ABI-encode the call: recordEvent(string,bytes32)
        $contract = $this->getContract();
        $callData = $contract->getData('recordEvent', $eventType, $hashBytes32);
        if (!$callData) {
            throw new \RuntimeException('Failed to encode recordEvent call data');
        }
        if (!str_starts_with($callData, '0x')) {
            $callData = '0x' . $callData;
        }

        // 2. Get the sender's current nonce
        $nonce = $this->getTransactionCount($fromAddress);

        // 3. Build & sign the transaction
        $tx = new Transaction([
            'nonce'    => '0x' . dechex($nonce),
            'from'     => $fromAddress,
            'to'       => $contractAddress,
            'gas'      => '0x' . dechex($gasLimit),
            'gasPrice' => '0x' . dechex($gasPrice),
            'value'    => '0x0',
            'data'     => $callData,
            'chainId'  => $chainId,
        ]);


        // Defensive cleanup: strip whitespace, quotes, optional 0x prefix.
        $privateKey = trim($fromKey, " \t\n\r\"'");
            if (str_starts_with($privateKey, '0x') || str_starts_with($privateKey, '0X')) {
        $privateKey = substr($privateKey, 2);
        }
        // Must be exactly 64 hex chars
            if (!preg_match('/^[0-9a-fA-F]{64}$/', $privateKey)) {
        throw new \RuntimeException(
        'Invalid QUORUM_FROM_KEY: expected 64 hex characters (with optional 0x prefix), got ' .
        strlen($privateKey) . ' chars.'
    );
    }
        $signed     = $tx->sign($privateKey);

        // 4. Send via eth_sendRawTransaction
        $txHash = $this->sendRawTransaction('0x' . $signed);

        return $txHash;
    }

    /*Build (and cache) the Web3 client.*/

    protected function getWeb3(): Web3
    {
        if ($this->web3 === null) {
            // Default web3.php timeout is too short for permissioned chains
            // with 5s block times. Use an HttpProvider with a longer timeout.
            $provider = new \Web3\Providers\HttpProvider(
                new \Web3\RequestManagers\HttpRequestManager(
                    config('blockchain.node_url'),
                    30   // 30 seconds — generous for slow chains
                )
            );
            $this->web3 = new Web3($provider);
        }
        return $this->web3;
    }

    /*Build (and cache) the CrisisAudit Contract handle.
     */
    protected function getContract(): Contract
    {
        if ($this->contract === null) {
            $abi = config('blockchain_abi.crisis_audit');
            if (!$abi) {
                throw new \RuntimeException('blockchain_abi.crisis_audit config is missing');
            }
            $this->contract = (new Contract($this->getWeb3()->provider, $abi))
                ->at(config('blockchain.contract_address'));
        }
        return $this->contract;
    }

    /*Fetch eth_getTransactionCount("pending") for an address (the nonce).*/

    protected function getTransactionCount(string $address): int
    {
        $count = null;
        $err = null;
        $this->getWeb3()->eth->getTransactionCount($address, 'pending', function ($error, $result) use (&$count, &$err) {
            if ($error) {
                $err = $error;
                return;
            }
            $count = $result;
        });
        if ($err) {
            throw new \RuntimeException('eth_getTransactionCount failed: ' . $err->getMessage());
        }
        if ($count === null) {
            throw new \RuntimeException('eth_getTransactionCount returned no result');
        }
        // $count is a BigInteger
        return (int) (string) $count->toString();
    }

    /*eth_sendRawTransaction — returns the tx hash.*/

    protected function sendRawTransaction(string $signedHex): string
    {
        $hash = null;
        $err = null;
        $this->getWeb3()->eth->sendRawTransaction($signedHex, function ($error, $result) use (&$hash, &$err) {
            if ($error) {
                $err = $error;
                return;
            }
            $hash = $result;
        });
        if ($err) {
            throw new \RuntimeException('eth_sendRawTransaction failed: ' . $err->getMessage());
        }
        if (!$hash) {
            throw new \RuntimeException('eth_sendRawTransaction returned no result');
        }
        return $hash;
    }


    //  Internal — helpers


    protected function isEventEligibleForChain(string $eventType): bool
    {
        $allowed = (array) config('blockchain.quorum_enabled_events', []);
        return in_array($eventType, $allowed, true);
    }

    protected function isChainConfigured(): bool
    {
        return (bool) config('blockchain.node_url')
            && (bool) config('blockchain.contract_address')
            && (bool) config('blockchain.from_address')
            && (bool) config('blockchain.from_key');
    }

    protected function canonicalize($val)
    {
        if (is_array($val)) {
            ksort($val);
            foreach ($val as $k => $v) {
                $val[$k] = $this->canonicalize($v);
            }
        }
        return $val;
    }
}
