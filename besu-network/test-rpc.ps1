# Quick verification that the network is up and producing blocks.

Write-Host ""
Write-Host "Testing JSON-RPC on http://localhost:8545..." -ForegroundColor Cyan
Write-Host ""

# 1. Latest block number
$body = '{"jsonrpc":"2.0","method":"eth_blockNumber","params":[],"id":1}'
try {
    $resp = Invoke-RestMethod -Uri "http://localhost:8545" -Method Post -ContentType "application/json" -Body $body -TimeoutSec 5
    $blockHex = $resp.result
    $blockNum = [Convert]::ToInt64($blockHex.Substring(2), 16)
    Write-Host "Latest block number : $blockNum  ($blockHex)" -ForegroundColor Green
} catch {
    Write-Host "ERROR: Could not connect to RPC. Is the network running?" -ForegroundColor Red
    Write-Host "Try: docker-compose ps" -ForegroundColor Red
    exit 1
}

# 2. Chain ID
$body = '{"jsonrpc":"2.0","method":"eth_chainId","params":[],"id":2}'
$resp = Invoke-RestMethod -Uri "http://localhost:8545" -Method Post -ContentType "application/json" -Body $body
$chainIdHex = $resp.result
$chainId = [Convert]::ToInt64($chainIdHex.Substring(2), 16)
Write-Host "Chain ID            : $chainId" -ForegroundColor Green

# 3. Number of connected peers
$body = '{"jsonrpc":"2.0","method":"net_peerCount","params":[],"id":3}'
$resp = Invoke-RestMethod -Uri "http://localhost:8545" -Method Post -ContentType "application/json" -Body $body
$peersHex = $resp.result
$peers = [Convert]::ToInt64($peersHex.Substring(2), 16)
Write-Host "Connected peers     : $peers  (should be 3)" -ForegroundColor Green

# 4. QBFT validators
$body = '{"jsonrpc":"2.0","method":"qbft_getValidatorsByBlockNumber","params":["latest"],"id":4}'
$resp = Invoke-RestMethod -Uri "http://localhost:8545" -Method Post -ContentType "application/json" -Body $body
Write-Host "QBFT validators     :" -ForegroundColor Green
foreach ($v in $resp.result) {
    Write-Host "  $v"
}

# 5. Pre-funded account balance
$body = '{"jsonrpc":"2.0","method":"eth_getBalance","params":["0x88495ee2af2cb16bc4ed1a76c91c8f5c2b8b3b96","latest"],"id":5}'
$resp = Invoke-RestMethod -Uri "http://localhost:8545" -Method Post -ContentType "application/json" -Body $body
$balanceHex = $resp.result
Write-Host "Funded account balance: $balanceHex" -ForegroundColor Green

Write-Host ""
if ($blockNum -gt 0) {
    Write-Host "Network is producing blocks. Ready for Hardhat in Step 11." -ForegroundColor Cyan
} else {
    Write-Host "Network is up but block 0 = no blocks produced yet." -ForegroundColor Yellow
    Write-Host "Wait ~10 seconds and re-run this script." -ForegroundColor Yellow
}
Write-Host ""
