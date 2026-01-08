# Specification: Digital to Physical Archive Transfer

## Feature Description
Allow users to transfer digital documents/folders to physical archive records with automatic cleanup of digital assets after successful transfer.

## User Story
As an archivist, I want to transfer digital copies of documents to their corresponding physical records in the archive management system so that I can consolidate digital and physical assets and maintain a clean digital storage.

## Acceptance Criteria
- User can initiate transfer from digital document show page
- User can initiate transfer from digital folder show page
- User can select a specific physical record to associate with
- Transfer shows success/error feedback
- Digital asset is automatically deleted after confirmed transfer
- Transfer history is logged
- Only authorized users can perform transfers

## Requirements

### Functional Requirements
1. **Transfer Button**: Add prominent button on digital document/folder show pages
2. **Modal Dialog**: Display modal to select target physical record
3. **Record Selection**: Search and select from available physical records
4. **Confirmation**: Ask user to confirm before performing destructive operation
5. **Async Processing**: Handle transfer asynchronously for large documents
6. **Audit Trail**: Log all transfer operations with user, timestamp, and metadata
7. **Cleanup**: Automatically delete digital asset after confirmed transfer
8. **Metadata Preservation**: Store transfer metadata (from, to, when, by whom)

### Non-Functional Requirements
1. **Performance**: Transfer must complete within 30 seconds for typical documents
2. **Reliability**: Support rollback if transfer fails
3. **Security**: Only authorized users can transfer/delete content
4. **Atomicity**: Transfer and deletion must be atomic operation
5. **Auditability**: All transfers must be logged and traceable

## API Endpoints

### GET /api/record-digital-transfer/form
Get transfer form data including available physical records
- **Query Parameters:**
  - `type`: 'document' | 'folder'
  - `id`: Digital resource ID
- **Response:** List of physical records with metadata

### POST /api/record-digital-transfer
Process transfer request
- **Body:**
  ```json
  {
    "type": "document|folder",
    "digital_id": 123,
    "physical_id": 456,
    "notes": "optional notes"
  }
  ```
- **Response:** Transfer result with metadata

### DELETE /api/record-digital-transfer/{id}
Cancel pending transfer
- **Response:** Cancellation status

## Data Structure

### Transfer Metadata (JSON)
```json
{
  "transferred_at": "2026-01-08T10:30:00Z",
  "transferred_by_user_id": 1,
  "transferred_to_record_id": 456,
  "original_digital_id": 123,
  "digital_type": "document|folder",
  "transferred_files_count": 5,
  "transferred_size_bytes": 102400,
  "notes": "archived"
}
```

## Integration Points
- Digital Document Show View
- Digital Folder Show View
- Record Physical Show View (display linked digital metadata)
- Audit Log System

## Error Scenarios
1. **Invalid Transfer**: Physical record doesn't exist
2. **Permission Denied**: User lacks authorization
3. **Validation Failed**: Digital content doesn't meet transfer criteria
4. **Database Error**: Transaction fails, automatic rollback
5. **File Deletion Failed**: Digital asset can't be deleted

## Success Metrics
- Transfer completes in <30 seconds
- 100% audit trail coverage
- Zero data loss or orphaned records
- Users report clear feedback on operation status
