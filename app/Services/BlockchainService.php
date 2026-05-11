<?php

namespace App\Services;

use App\Models\Blockchain;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BlockchainService
 *
 * Records hashes of sensitive crisis events on a permissioned Quorum
 * blockchain network. Sensitive payloads remain OFF-CHAIN in MySQL;
 * only SHA-256 hashes are sent ON-CHAIN.
 *
 * Modes:
 *  - quorum     : real JSON-RPC call to a Quorum node, persists tx_hash
 *  - simulation : MySQL-only record (used when Quorum is unreachable
 *                 or for local development)
 *
 * The service auto-falls back to simulation mode if the Quorum node is
 * unreachable, so the application never breaks.
 *
 * Trigger points (event types):
 *   CRISIS_VERIFIED
 *   DEATH_CONFIRMED
 *   LDMS_TRIGGERED
 *   DONATION_RECORDED
 *   REPORT_REJECTED
 */
class BlockchainService
{
    /**
     * Compute SHA-256 hash of a canonical JSON representation of $data.
     * Keys are recursively sorted so identical content always hashes the same.
     */
    public function generateHash(array $data): string
    {
        $canonical = $this->canonicalize($data);
        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return hash('sha256', $json);
    }

    protected function canonicalize($val)
    {
        if (is_array($val)) {
            ksort($val);
            foreach ($val as $k => $v) $val[$k] = $this->canonicalize($v);
        }
        return $val;
    }

    /**
     * Record a hashed event on the blockchain.
     *
     * @param  string $eventType   One of CRISIS_VERIFIED, DEATH_CONFIRMED, LDMS_TRIGGERED, DONATION_RECORDED, REPORT_REJECTED
     * @param  array  $eventData   Off-chain data — only its hash goes on-chain
     * @param  int|string|null $referenceId  Optional reference id for cross-link
     * @param  string|null $referenceTable
     * @return array               ['blockchain_id'=>int,'hash'=>string,'tx_hash'=>?string,'mode'=>string]
     */
    public function recordEvent(string $eventType, array $eventData, int|string|null $referenceId = null, ?string $referenceTable = null): array
    {
        $hash = $this->generateHash($eventData);
        $txHash = null;
        $mode = 'simulation';

        // ---------- Real Quorum integration ----------
        // The following block sends eth_sendTransaction to a Quorum node when
        // QUORUM_NODE_URL is configured. Replace this block with full Web3 PHP
        // contract bindings (sc-0xCrisisAudit.recordEvent) in production.
        if (config('blockchain.node_url')) {
            try {
                $txHash = $this->sendToQuorum($eventType, $hash);
                if ($txHash) $mode = 'quorum';
            } catch (\Throwable $e) {
                Log::warning('Quorum unreachable, falling back to simulation', [
                    'event' => $eventType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        // -----------------------------------------------

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
            'reference_id'    => is_numeric($referenceId) ? (int)$referenceId : null,
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
     * Send a transaction to the configured Quorum node.
     * NOTE: This uses a simplified eth_sendTransaction call. In production,
     * use a Web3 PHP library to ABI-encode a call to CrisisAudit.recordEvent.
     */
    public function sendToQuorum(string $eventType, string $hashHex): ?string
    {
        $nodeUrl = config('blockchain.node_url');
        $from    = config('blockchain.from_address');
        $to      = config('blockchain.contract_address');

        if (!$nodeUrl || !$from || !$to) return null;

        // Function selector for recordEvent(string,bytes32) — keccak256 first 4 bytes.
        // Hard-coded here as a placeholder: in production compute via ethereum-tx tools.
        $selector = '0x' . substr(hash('sha256', 'recordEvent(string,bytes32)'), 0, 8);

        // Naive payload: selector + zero-padded eventType length + content + hash bytes32.
        // Production code should ABI-encode properly.
        $payload  = $selector
                  . str_pad(bin2hex($eventType), 64, '0', STR_PAD_RIGHT)
                  . str_pad($hashHex, 64, '0', STR_PAD_LEFT);

        $resp = Http::timeout(10)->post($nodeUrl, [
            'jsonrpc' => '2.0',
            'method'  => 'eth_sendTransaction',
            'params'  => [[
                'from' => $from,
                'to'   => $to,
                'data' => $payload,
                'gas'  => '0x76c0',
            ]],
            'id' => 1,
        ]);

        if (!$resp->successful()) return null;
        return $resp->json('result');
    }

    /**
     * Recompute hash of $originalData and compare to the stored record's hash.
     */
    public function verifyHash(int $blockchainId, array $originalData): bool
    {
        $record = Blockchain::find($blockchainId);
        if (!$record) return false;
        return hash_equals($record->stored_data, $this->generateHash($originalData));
    }

    /**
     * Verify a free-form hash against the stored blockchain table.
     */
    public function verifyHashString(string $hash): ?Blockchain
    {
        return Blockchain::where('stored_data', $hash)->first();
    }

    public function getAuditLog(): Collection
    {
        return Blockchain::orderByDesc('timestamp')->limit(500)->get();
    }
}
