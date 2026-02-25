{{-- Shared Deposit Module Styles - Used across buildings, floors, rooms, shelves views --}}
@push('styles')
<style>
/* ═══════════════════════════════════════════════
   Deposit Module - Design System
   ═══════════════════════════════════════════════ */

/* Header */
.deposit-header {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 0.5rem;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

.deposit-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.deposit-subtitle {
    color: #64748b;
    margin: 0.25rem 0 0 0;
    font-size: 0.85rem;
}

/* Breadcrumb */
.deposit-breadcrumb {
    background: #ffffff;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    margin-bottom: 1rem;
}

.deposit-breadcrumb .breadcrumb {
    margin: 0;
    font-size: 0.85rem;
}

/* Compact stat bar */
.deposit-stats {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.deposit-stat {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 0.6rem 1rem;
    text-align: center;
    flex: 1;
    min-width: 120px;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
}

.deposit-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}

.deposit-stat-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
}

/* Info card (sidebar) */
.deposit-info-card {
    background: #ffffff;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    height: 100%;
    overflow: hidden;
}

.deposit-info-card .card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
}

.deposit-info-card .card-header h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.deposit-info-card .card-body {
    padding: 1rem;
}

.deposit-info-card .card-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
}

/* Info items in sidebar */
.deposit-info-item {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    margin-bottom: 0.6rem;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 0.375rem;
    border-left: 3px solid #0891b2;
    font-size: 0.85rem;
}

.deposit-info-item:last-child {
    margin-bottom: 0;
}

.deposit-info-item i {
    width: 18px;
    text-align: center;
    color: #0891b2;
    margin-top: 2px;
}

.deposit-info-item .info-label {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.8rem;
}

.deposit-info-item .info-value {
    color: #64748b;
    font-size: 0.85rem;
}

/* List section (right panel) */
.deposit-list-section {
    background: #ffffff;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    overflow: hidden;
}

.deposit-list-header {
    background: linear-gradient(135deg, #0891b2 0%, #0ea5e9 100%);
    color: white;
    padding: 0.75rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.deposit-list-header h5 {
    margin: 0;
    font-size: 1rem;
}

.deposit-list-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.2rem 0.6rem;
    border-radius: 1rem;
    font-size: 0.8rem;
    font-weight: 600;
}

/* List items */
.deposit-list-item {
    border-bottom: 1px solid #e2e8f0;
    padding: 0.75rem 1rem;
    transition: all 0.15s ease;
    background: #ffffff;
}

.deposit-list-item:hover {
    background: #f8fafc;
    padding-left: 1.25rem;
}

.deposit-list-item:last-child {
    border-bottom: none;
}

.deposit-list-item h6 {
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.deposit-list-item .text-muted {
    font-size: 0.8rem;
}

/* Empty state */
.deposit-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #64748b;
}

.deposit-empty i {
    font-size: 2.5rem;
    margin-bottom: 0.75rem;
    opacity: 0.4;
}

/* Occupancy bar */
.deposit-occupancy {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}

.deposit-occupancy-bar {
    flex: 1;
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
}

.deposit-occupancy-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease;
}

/* Table view for index pages */
.deposit-table {
    font-size: 0.875rem;
}

.deposit-table th {
    background: #f8fafc;
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    color: #64748b;
    border-bottom: 2px solid #e2e8f0;
    padding: 0.6rem 0.75rem;
}

.deposit-table td {
    padding: 0.6rem 0.75rem;
    vertical-align: middle;
}

.deposit-table tbody tr {
    transition: background 0.15s ease;
}

.deposit-table tbody tr:hover {
    background: #f8fafc;
}

/* Responsive */
@media (max-width: 768px) {
    .deposit-header {
        padding: 0.75rem;
    }
    .deposit-title {
        font-size: 1.2rem;
    }
    .deposit-stats {
        flex-direction: column;
    }
    .deposit-stat {
        min-width: auto;
    }
}
</style>
@endpush
