require("@nomicfoundation/hardhat-toolbox");
const fs = require("fs");
const path = require("path");

/**
 * Read node 1's private key from the besu-network folder.
 * Node 1 is pre-funded with ~10^9 ETH in our genesis, so we use it
 * as the deployer account.
 */
function readDeployerKey() {
  const keyPath = path.resolve(__dirname, "..", "besu-network", "nodes", "node1", "key");
  if (!fs.existsSync(keyPath)) {
    throw new Error(
      `Cannot find node 1 private key at: ${keyPath}\n` +
      `Make sure the besu-network is set up at C:\\xampp\\htdocs\\besu-network`
    );
  }
  const raw = fs.readFileSync(keyPath, "utf8").trim();
  // Besu's key file has no 0x prefix; ethers expects one.
  return raw.startsWith("0x") ? raw : "0x" + raw;
}

/** @type import('hardhat/config').HardhatUserConfig */
module.exports = {
  solidity: {
    version: "0.8.20",
    settings: {
      optimizer: {
        enabled: true,
        runs: 200,
      },
    },
  },

  networks: {
    // Local in-memory chain for unit testing
    hardhat: {
      chainId: 31337,
    },

    // Our actual Besu QBFT network (running in Docker via besu-network/)
    besu: {
      url: "http://172.17.0.1:8545",
      chainId: 1337,
      accounts: [readDeployerKey()],
      // QBFT block time is ~5s; give transactions room to be mined
      timeout: 60000,
      gas: 8000000,
      gasPrice: 0, // free on private chain
    },
  },
};