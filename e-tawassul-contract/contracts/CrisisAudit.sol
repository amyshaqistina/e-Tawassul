// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

/**
 * @title CrisisAudit
 * @notice Tamper-evident audit log for the e-Tawassul Crisis Response System.
 *
 * Records SHA-256 hashes of off-chain crisis events. Only hashes go on-chain —
 * the underlying data (student records, descriptions, NOK details, etc.) stays
 * in the off-chain MySQL database for privacy and PDPA compliance.
 *
 * Event types currently anchored:
 *   - CRISIS_VERIFIED
 *   - REPORT_REJECTED
 *   - DEATH_CONFIRMED
 *   - LDMS_TRIGGERED
 *
 * DONATION_RECORDED reserved for Phase 2 (after payment provider integration).
 */
contract CrisisAudit {

    struct AuditEntry {
        bytes32 hash;          // SHA-256 of the off-chain event payload
        string  eventType;     // e.g. "CRISIS_VERIFIED"
        uint256 timestamp;     // block timestamp when recorded
        address recordedBy;    // sender that submitted the entry
    }

    /// @notice Sequential entry id -> full record
    mapping(uint256 => AuditEntry) public entries;

    /// @notice Reverse lookup: given a hash, find its entry id (0 if not found)
    mapping(bytes32 => uint256) public hashToId;

    /// @notice Total number of entries ever recorded
    uint256 public entryCount;

    /// @notice Emitted on every recordEvent call. The `id` and `hash` are
    ///         indexed so off-chain listeners can filter efficiently.
    event EventRecorded(
        uint256 indexed id,
        bytes32 indexed hash,
        string  eventType,
        address indexed recordedBy,
        uint256 timestamp
    );

    /**
     * @notice Record a new audit entry.
     * @param eventType  Human-readable event type, e.g. "CRISIS_VERIFIED"
     * @param hash       SHA-256 hash of the off-chain payload (as bytes32)
     * @return id        The newly-assigned entry id
     */
    function recordEvent(string calldata eventType, bytes32 hash)
        external
        returns (uint256 id)
    {
        require(bytes(eventType).length > 0, "eventType required");
        require(hash != bytes32(0), "hash required");
        require(hashToId[hash] == 0, "hash already recorded");

        entryCount += 1;
        id = entryCount;

        entries[id] = AuditEntry({
            hash:       hash,
            eventType:  eventType,
            timestamp:  block.timestamp,
            recordedBy: msg.sender
        });
        hashToId[hash] = id;

        emit EventRecorded(id, hash, eventType, msg.sender, block.timestamp);
    }

    /**
     * @notice Retrieve a single audit entry by its id.
     */
    function getEntry(uint256 id)
        external
        view
        returns (
            bytes32 hash,
            string memory eventType,
            uint256 timestamp,
            address recordedBy
        )
    {
        require(id > 0 && id <= entryCount, "id out of range");
        AuditEntry memory e = entries[id];
        return (e.hash, e.eventType, e.timestamp, e.recordedBy);
    }

    /**
     * @notice Check whether a given hash has been recorded.
     * @return exists  True if the hash exists in the log
     * @return id      The entry id if found, otherwise 0
     */
    function verifyHash(bytes32 hash)
        external
        view
        returns (bool exists, uint256 id)
    {
        id = hashToId[hash];
        exists = (id != 0);
    }
}