# =============================================================================
#  e-Tawassul Besu QBFT network bootstrap (v2 — funds node 1 for deployments)
#
#  1. Runs Besu in a one-shot Docker container to generate 4 validator
#     keypairs and a QBFT genesis with them baked in as initial validators.
#  2. Patches the generated genesis to ALSO pre-fund node 1's address with
#     10^9 ETH, so we can use node 1's key to deploy contracts.
#  3. Reorganises Besu's output into the docker-compose layout.
#  4. Builds static-nodes.json.
#
#  Re-running wipes existing keys & chain data.
# =============================================================================

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "=== e-Tawassul: Besu QBFT Network Setup (v2) ===" -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path "docker-compose.yml")) {
    Write-Host "ERROR: run this script from inside the besu-network folder." -ForegroundColor Red
    exit 1
}
if (-not (Test-Path "config/qbftConfigFile.json")) {
    Write-Host "ERROR: config/qbftConfigFile.json not found." -ForegroundColor Red
    exit 1
}

if (Test-Path "nodes/node1/key") {
    Write-Host "WARNING: Existing keys found. Re-running will replace them" -ForegroundColor Yellow
    Write-Host "and wipe the chain when docker-compose restarts." -ForegroundColor Yellow
    $reply = Read-Host "Continue? (y/N)"
    if ($reply -ne "y" -and $reply -ne "Y") { Write-Host "Aborted."; exit 0 }
}

if (Test-Path "nodes")     { Remove-Item -Recurse -Force "nodes" }
if (Test-Path "generated") { Remove-Item -Recurse -Force "generated" }
New-Item -ItemType Directory -Path "nodes"     | Out-Null
New-Item -ItemType Directory -Path "generated" | Out-Null

Write-Host "Step 1/4: Generating validator keypairs and genesis via Besu..." -ForegroundColor Green
Write-Host ""

$pwd_path = (Get-Location).Path -replace "\\","/"

docker run --rm `
    -v "${pwd_path}/config:/config:ro" `
    -v "${pwd_path}/generated:/output" `
    hyperledger/besu:24.1.2 `
    operator generate-blockchain-config `
    --config-file=/config/qbftConfigFile.json `
    --to=/output `
    --private-key-file-name=key

if ($LASTEXITCODE -ne 0) {
    Write-Host "ERROR: Besu key generation failed (exit $LASTEXITCODE)." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 2/4: Reorganising generated files..." -ForegroundColor Green

Copy-Item "generated/genesis.json" "config/genesis.json" -Force

$keyDirs = Get-ChildItem -Directory -Path "generated/keys"
if ($keyDirs.Count -ne 4) {
    Write-Host "ERROR: expected 4 key folders, found $($keyDirs.Count)" -ForegroundColor Red
    exit 1
}

$nodeAddresses = @{}
$i = 1
foreach ($d in $keyDirs) {
    $nodeName = "node$i"
    $nodeDir  = "nodes/$nodeName"
    New-Item -ItemType Directory -Path $nodeDir -Force | Out-Null
    Copy-Item "$($d.FullName)/key"     "$nodeDir/key"     -Force
    Copy-Item "$($d.FullName)/key.pub" "$nodeDir/key.pub" -Force
    $address = $d.Name
    Set-Content -Path "$nodeDir/address.txt" -Value $address -NoNewline
    $nodeAddresses[$nodeName] = $address
    Write-Host "  $nodeName -> $address"
    $i++
}

# -------------------------------------------------------------------
# Step 3: Patch genesis to fund node 1
# -------------------------------------------------------------------
Write-Host ""
Write-Host "Step 3/4: Pre-funding node 1 for contract deployment..." -ForegroundColor Green

$node1Addr = $nodeAddresses["node1"]
$genesis = Get-Content "config/genesis.json" -Raw | ConvertFrom-Json

# Convert PSCustomObject's alloc to a hashtable we can add to.
$allocHashtable = @{}
if ($genesis.alloc) {
    foreach ($prop in $genesis.alloc.PSObject.Properties) {
        $allocHashtable[$prop.Name] = $prop.Value
    }
}

# Add node 1 with 10^9 ETH (= 10^27 wei). One billion ETH is plenty for any dev work.
$allocHashtable[$node1Addr] = [PSCustomObject]@{
    balance = "1000000000000000000000000000"
}

# Rebuild the genesis with the new alloc
$newGenesis = [ordered]@{}
foreach ($prop in $genesis.PSObject.Properties) {
    if ($prop.Name -eq "alloc") {
        $newGenesis["alloc"] = $allocHashtable
    } else {
        $newGenesis[$prop.Name] = $prop.Value
    }
}
if (-not $newGenesis.Contains("alloc")) {
    $newGenesis["alloc"] = $allocHashtable
}

$newGenesis | ConvertTo-Json -Depth 20 | Out-File -FilePath "config/genesis.json" -Encoding ascii
Write-Host "  Node 1 ($node1Addr) funded with 10^9 ETH"

# -------------------------------------------------------------------
# Step 4: Build static-nodes.json
# -------------------------------------------------------------------
Write-Host ""
Write-Host "Step 4/4: Building static-nodes.json..." -ForegroundColor Green

$ips = @("172.16.239.11", "172.16.239.12", "172.16.239.13", "172.16.239.14")
$enodes = @()
for ($i = 1; $i -le 4; $i++) {
    $pub = (Get-Content -Path "nodes/node$i/key.pub" -Raw).Trim()
    if ($pub.StartsWith("0x")) { $pub = $pub.Substring(2) }
    $enodes += "enode://$pub@$($ips[$i-1]):30303"
}
$enodes | ConvertTo-Json | Out-File -FilePath "config/static-nodes.json" -Encoding ascii

Write-Host ""
Write-Host "=== Setup complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Validator addresses:" -ForegroundColor Yellow
for ($i = 1; $i -le 4; $i++) {
    $addr = Get-Content -Path "nodes/node$i/address.txt" -Raw
    Write-Host "  node$i : $addr"
}
Write-Host ""
Write-Host "Funded deploy account (use this in Hardhat):" -ForegroundColor Yellow
Write-Host "  Address     : $($nodeAddresses['node1'])"
Write-Host "  Private key : nodes/node1/key  (cat this file in Hardhat config)"
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. docker-compose up -d"
Write-Host "  2. PowerShell -ExecutionPolicy Bypass -File .\test-rpc.ps1"
Write-Host ""
