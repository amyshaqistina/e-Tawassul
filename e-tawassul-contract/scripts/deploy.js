/**
 * Deploy CrisisAudit.sol to the Besu QBFT network.
 *
 * Run with:
 *   npx hardhat run scripts/deploy.js --network besu
 *
 * After deployment, copy the printed contract address into Laravel's .env:
 *   QUORUM_CONTRACT_ADDRESS=0x...
 *   QUORUM_FROM_ADDRESS=0x...   (the deployer)
 */

const fs = require("fs");
const path = require("path");
const hre = require("hardhat");

async function main() {
    const [deployer] = await hre.ethers.getSigners();
    const network = hre.network.name;
    const balance = await hre.ethers.provider.getBalance(deployer.address);

    console.log("");
    console.log("======================================================");
    console.log("  CrisisAudit deployment");
    console.log("======================================================");
    console.log(`  Network    : ${network}`);
    console.log(`  Deployer   : ${deployer.address}`);
    console.log(`  Balance    : ${hre.ethers.formatEther(balance)} ETH`);
    console.log("");

    if (balance === 0n) {
        throw new Error(
            "Deployer has zero balance. Re-run setup.ps1 in besu-network/ and try again."
        );
    }

    console.log("Compiling (if needed) and deploying CrisisAudit...");
    const CrisisAudit = await hre.ethers.getContractFactory("CrisisAudit");
    const contract = await CrisisAudit.deploy();

    console.log(`Deploy tx hash : ${contract.deploymentTransaction().hash}`);
    console.log("Waiting for confirmation...");

    await contract.waitForDeployment();
    const address = await contract.getAddress();

    // Pull a few facts from the chain to confirm it's really deployed
    const deployedCode = await hre.ethers.provider.getCode(address);
    const codeBytes = (deployedCode.length - 2) / 2; // strip "0x", 2 hex chars per byte
    const entryCount = await contract.entryCount();

    console.log("");
    console.log("======================================================");
    console.log("  Deployment successful ✅");
    console.log("======================================================");
    console.log(`  Contract address  : ${address}`);
    console.log(`  Bytecode on chain : ${codeBytes} bytes`);
    console.log(`  Initial entryCount: ${entryCount}`);
    console.log("");
    console.log("Next step — add these to your Laravel .env file:");
    console.log("");
    console.log(`  QUORUM_NODE_URL=http://127.0.0.1:8545`);
    console.log(`  QUORUM_CONTRACT_ADDRESS=${address}`);
    console.log(`  QUORUM_FROM_ADDRESS=${deployer.address}`);
    console.log(`  QUORUM_CHAIN_ID=1337`);
    console.log("");

    // Save deployment info to a JSON file so Laravel deploy scripts can pick it up later
    const out = {
        network,
        chainId: 1337,
        contractAddress: address,
        deployer: deployer.address,
        deploymentTxHash: contract.deploymentTransaction().hash,
        deployedAt: new Date().toISOString(),
        bytecodeSize: codeBytes,
    };
    const outDir = path.resolve(__dirname, "..", "deployments");
    fs.mkdirSync(outDir, { recursive: true });
    const outPath = path.join(outDir, `${network}.json`);
    fs.writeFileSync(outPath, JSON.stringify(out, null, 2));
    console.log(`Deployment record saved to: ${outPath}`);
    console.log("");
}

main().catch((error) => {
    console.error("");
    console.error("Deployment failed:");
    console.error(error);
    process.exitCode = 1;
});