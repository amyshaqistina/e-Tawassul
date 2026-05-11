// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

/**
 * @title CrisisAudit
 * @notice Permissioned audit log for the e-Tawassul Crisis Response System.
 *
 * This contract records ONLY cryptographic hashes (SHA-256, supplied off-chain
 * by the BlockchainService) of crisis-related events. Sensitive personal data
 * lives off-chain in the application database; the chain provides tamper-
 * evident provenance.
 *
 * Intended deployment: Quorum permissioned network (no public mainnet).
 *
 * Recognized event types (passed as `_eventType` string):
 *   - "CRISIS_VERIFIED"
 *   - "DEATH_CONFIRMED"
 *   - "LDMS_TRIGGERED"
 *   - "DONATION_RECORDED"
 *   - "REPORT_REJECTED"
 */
contract CrisisAudit {
    address public owner;
    mapping(address => bool) public recorders;

    struct Record {
        string  eventType;
        bytes32 dataHash;
        uint256 timestamp;
        address recordedBy;
        bool    verified;
    }

    Record[] private records;

    event AuditRecorded(
        uint256 indexed id,
        string  indexed eventType,
        bytes32 indexed dataHash,
        uint256 timestamp,
        address recordedBy
    );

    event RecorderUpdated(address indexed account, bool enabled);

    modifier onlyOwner() {
        require(msg.sender == owner, "CrisisAudit: not owner");
        _;
    }

    modifier onlyRecorder() {
        require(recorders[msg.sender] || msg.sender == owner, "CrisisAudit: not a recorder");
        _;
    }

    constructor() {
        owner = msg.sender;
        recorders[msg.sender] = true;
    }

    /**
     * @notice Grant or revoke recorder rights.
     */
    function setRecorder(address account, bool enabled) external onlyOwner {
        recorders[account] = enabled;
        emit RecorderUpdated(account, enabled);
    }

    /**
     * @notice Record a hashed event on-chain.
     * @param _eventType String code of the event (e.g. "CRISIS_VERIFIED").
     * @param _dataHash  bytes32 SHA-256 of canonical JSON of off-chain data.
     * @return id        Sequential ID assigned to the record.
     */
    function recordEvent(string calldata _eventType, bytes32 _dataHash)
        external
        onlyRecorder
        returns (uint256 id)
    {
        require(bytes(_eventType).length > 0, "CrisisAudit: empty event type");
        require(_dataHash != bytes32(0),       "CrisisAudit: empty hash");

        records.push(Record({
            eventType: _eventType,
            dataHash:  _dataHash,
            timestamp: block.timestamp,
            recordedBy: msg.sender,
            verified:  true
        }));
        id = records.length - 1;

        emit AuditRecorded(id, _eventType, _dataHash, block.timestamp, msg.sender);
    }

    /**
     * @notice Verify a record's stored hash matches a candidate hash.
     */
    function verifyRecord(uint256 _id, bytes32 _dataHash) external view returns (bool) {
        require(_id < records.length, "CrisisAudit: id out of range");
        return records[_id].dataHash == _dataHash;
    }

    /**
     * @notice Retrieve a record by id.
     */
    function getRecord(uint256 _id)
        external
        view
        returns (string memory eventType, bytes32 dataHash, uint256 timestamp, bool verified)
    {
        require(_id < records.length, "CrisisAudit: id out of range");
        Record storage r = records[_id];
        return (r.eventType, r.dataHash, r.timestamp, r.verified);
    }

    function totalRecords() external view returns (uint256) {
        return records.length;
    }
}
