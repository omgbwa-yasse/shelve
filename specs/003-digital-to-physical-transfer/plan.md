# Plan: Digital to Physical Archive Transfer

## Overview
Transfer and associate digital documents/folders to physical archives with automatic cleanup of digital assets after successful transfer.

## 1. Technical Stack
- **Framework:** Laravel 12
- **Database:** MySQL
- **Frontend:** Blade/Vue.js
- **APIs:** RESTful endpoints for transfer operations

## 2. Architecture

### Controllers
- `RecordDigitalTransferController`: Handle transfer operations
  - `showTransferForm()`: Display transfer dialog/modal
  - `store()`: Process transfer and association
  - `destroy()`: Cancel pending transfer

### Models & Relationships
- `RecordDigitalFolder`: Add relationship to `RecordPhysical` (after transfer)
- `RecordDigitalDocument`: Add relationship to `RecordPhysical` (after transfer)
- `RecordPhysical`: Add metadata about transferred digital content

### Services
- `DigitalPhysicalTransferService`: Core business logic
  - `validateTransfer()`: Verify transfer eligibility
  - `associateDigitalToPhysical()`: Link digital to physical
  - `deleteDigitalAfterTransfer()`: Remove digital asset post-transfer

### Database
- Migration: Add transfer tracking fields
  - `record_digital_folders`: `transferred_at`, `transferred_to_record_id`, `transfer_metadata`
  - `record_digital_documents`: `transferred_at`, `transferred_to_record_id`, `transfer_metadata`
  - `record_physicals`: `linked_digital_content` (JSON metadata)

## 3. File Structure
```
app/
    Http/
        Controllers/
            RecordDigitalTransferController.php
    Services/
        DigitalPhysicalTransferService.php
resources/
    views/
        records/
            digital-documents/
                partials/transfer-button.blade.php
                partials/transfer-modal.blade.php
            digital-folders/
                partials/transfer-button.blade.php
                partials/transfer-modal.blade.php
tests/
    Feature/
        DigitalPhysicalTransferTest.php
    Unit/
        DigitalPhysicalTransferServiceTest.php
```

## 4. Implementation Steps

### Phase 1: Database & Models
- [ ] Create migration for transfer tracking fields
- [ ] Update `RecordDigitalFolder` and `RecordDigitalDocument` models
- [ ] Add accessors/mutators for transfer metadata

### Phase 2: Service Layer
- [ ] Create `DigitalPhysicalTransferService`
- [ ] Implement validation logic
- [ ] Implement association logic
- [ ] Implement cleanup logic

### Phase 3: Controller & Routes
- [ ] Create `RecordDigitalTransferController`
- [ ] Implement transfer endpoints
- [ ] Add routes to `routes/web.php` and `routes/api.php`

### Phase 4: Frontend Views
- [ ] Create transfer button component
- [ ] Create transfer modal component
- [ ] Integrate into digital-documents show view
- [ ] Integrate into digital-folders show view

### Phase 5: Testing
- [ ] Write feature tests for transfer operations
- [ ] Write unit tests for service layer
- [ ] Test error scenarios and validations

## 5. Key Features
- **Transfer Validation**: Ensure digital content is valid for transfer
- **Association Logic**: Link digital documents to specific physical records
- **Audit Trail**: Track who transferred what and when
- **Error Handling**: Graceful failure with rollback
- **User Feedback**: Clear status messages during transfer
