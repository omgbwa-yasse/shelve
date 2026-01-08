# Tasks: Digital to Physical Archive Transfer

## Phase 1: Database & Models
- [ ] Create migration for transfer tracking fields on digital tables <!-- id: 1 -->
- [ ] Update `RecordDigitalFolder` model with transfer attributes <!-- id: 2 -->
- [ ] Update `RecordDigitalDocument` model with transfer attributes <!-- id: 3 -->
- [ ] Add transfer relationship accessors to models <!-- id: 4, deps: 2,3 -->

## Phase 2: Service Layer
- [ ] Create `app/Services/DigitalPhysicalTransferService.php` <!-- id: 5 -->
- [ ] Implement `validateTransfer()` method <!-- id: 6, deps: 5 -->
- [ ] Implement `associateDigitalToPhysical()` method <!-- id: 7, deps: 5 -->
- [ ] Implement `deleteDigitalAfterTransfer()` method <!-- id: 8, deps: 5 -->
- [ ] Add transaction support and error handling <!-- id: 9, deps: 6,7,8 -->

## Phase 3: Controller & Routes
- [ ] Create `app/Http/Controllers/RecordDigitalTransferController.php` <!-- id: 10 -->
- [ ] Implement `showTransferForm()` method (API endpoint) <!-- id: 11, deps: 10 -->
- [ ] Implement `store()` method for processing transfer <!-- id: 12, deps: 10,5 -->
- [ ] Implement `cancel()` method for pending transfers <!-- id: 13, deps: 10 -->
- [ ] Add routes to `routes/api.php` <!-- id: 14, deps: 10,11,12,13 -->

## Phase 4: Frontend Views - Documents
- [ ] Create `resources/views/records/digital-documents/partials/transfer-button.blade.php` <!-- id: 15 -->
- [ ] Create `resources/views/records/digital-documents/partials/transfer-modal.blade.php` <!-- id: 16 -->
- [ ] Integrate transfer button into document show view <!-- id: 17, deps: 15 -->
- [ ] Integrate transfer modal into document show view <!-- id: 18, deps: 16 -->
- [ ] Add JavaScript for transfer handling in documents <!-- id: 19, deps: 18 -->

## Phase 5: Frontend Views - Folders
- [ ] Create `resources/views/records/digital-folders/partials/transfer-button.blade.php` <!-- id: 20 -->
- [ ] Create `resources/views/records/digital-folders/partials/transfer-modal.blade.php` <!-- id: 21 -->
- [ ] Integrate transfer button into folder show view <!-- id: 22, deps: 20 -->
- [ ] Integrate transfer modal into folder show view <!-- id: 23, deps: 21 -->
- [ ] Add JavaScript for transfer handling in folders <!-- id: 24, deps: 23 -->

## Phase 6: Testing
- [ ] Create `tests/Feature/DigitalPhysicalTransferTest.php` <!-- id: 25 -->
- [ ] Write tests for transfer operations <!-- id: 26, deps: 25 -->
- [ ] Create `tests/Unit/DigitalPhysicalTransferServiceTest.php` <!-- id: 27 -->
- [ ] Write unit tests for service methods <!-- id: 28, deps: 27 -->
- [ ] Run and verify all tests pass <!-- id: 29, deps: 26,28 -->

## Phase 7: Documentation & Validation
- [ ] Add user documentation for transfer feature <!-- id: 30 -->
- [ ] Create validation checklist <!-- id: 31 -->
- [ ] Review and test all transfer scenarios <!-- id: 32, deps: 29 -->
