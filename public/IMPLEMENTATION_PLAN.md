# Implementation Plan - GeoS Ideal Rebuild (Option A: Wrapper Strategy)

**Strategy**: "Catalog First" with **Laravel as API Facade**. `bp_contracts` stays in WordPress.
**Goal**: Migrate public-facing catalog to Laravel + React. Keep business logic in WordPress until ready.
**Tech Stack**:
- **Backend**: Laravel 11 (Catalog API, Queue Workers, Helper Services)
- **Database**: PostgreSQL (Catalog Data) + MySQL (Users, Contracts remain)
- **Frontend**: React (Catalog) — embedded in WordPress pages
- **Legacy**: WordPress (Content Management + `bp_contracts` + Users)

---

## Key Decisions (Option A)

> [!IMPORTANT]
> **No User Migration**: `gi_new_users` stays in MySQL. Laravel does NOT manage authentication for business users in this phase.

> [!IMPORTANT]
> **`bp_contracts` Unchanged**: Continue development in WordPress. Laravel may provide helper services (queues, ERP sync) but does NOT replace the module.

> [!NOTE]
> **Deferred Items**: User migration and full `bp_contracts` rebuild are deferred until you explicitly signal readiness.

---

## Detailed Plugin Migration Roadmap

### Wave 1: Foundation & Primary Catalog (Weeks 1-8)
*Focus: Infrastructure & Kitchen Catalog.*

| Plugin | Current Role | Migration Strategy | Technical Implementation |
| :--- | :--- | :--- | :--- |
| **wordpress_framework** | DI Container / Base Models | **[KEEP]** | Remains in WordPress for `bp_contracts` dependency. |
| **bp_kitchen_add** | Admin: Kitchen CRUD | **[HYBRID]** | **Admin**: Stays in WP. <br>**Logic**: Syncs data to Laravel `Kitchen` model via API. |
| **bp_kitchen_print** | Frontend: Kitchen Catalog | **[REBUILD]** | Rebuilt as **React** `CatalogComponent`. Fetches data from Laravel API. |
| **bp_facades_list** | Admin: Facades | **[HYBRID]** | **Admin**: Stays in WP. <br>**Logic**: Syncs to Laravel `Facade` model. |

### Wave 2: Extended Products & Logic (Weeks 9-14)
*Focus: Completing the Product Catalog Data.*

| Plugin | Current Role | Migration Strategy | Technical Implementation |
| :--- | :--- | :--- | :--- |
| **bp_wardrobe_add** | Admin: Wardrobe CRUD | **[HYBRID]** | Similar to Kitchens. Syncs to Laravel `Wardrobe` model. |
| **bp_wardrobe_print** | Frontend: Wardrobe Catalog | **[REBUILD]** | Rebuilt as **React** component (reusing Kitchen logic). |
| **bp_related_products** | Living Room Products | **[REBUILD]** | Modeled as `Product` type in Laravel. Admin stays in WP. |
| **bp_materials** | Accessories/Materials | **[REBUILD]** | Migrated to Laravel. Admin UI synced from WP. |
| **bp_samples** | Exposition Samples | **[REBUILD]** | Laravel Model `Sample`. React Frontend. Admin synced from WP. |

### Wave 3: Dealer Resources (Weeks 15-18)
*Focus: Ancillary Dealer Tools — Safe, Non-Critical.*

| Plugin | Current Role | Migration Strategy | Technical Implementation |
| :--- | :--- | :--- | :--- |
| **bp_dealer_files** | File Sharing | **[REBUILD]** | **Backend**: Laravel Storage (S3/Local) + `FileResource` model. <br>**Frontend**: React "Download Center". |
| **bp_salons** | Map of Salons | **[REBUILD]** | **Backend**: `Salon` model (PostGIS optional). <br>**Frontend**: React Map Component (Leaflet/Google). |
| **bp_add_salon** | Frontend Form | **[REBUILD]** | React Form in Public site submitting to Laravel API. |

### Wave 4: Content & Marketing (Optional / Week 19+)
*Focus: Public Facing Content.*

| Plugin | Current Role | Migration Strategy | Technical Implementation |
| :--- | :--- | :--- | :--- |
| **bp_homepage** | Custom Home Layout | **[REBUILD]** | React Landing Page (Static or fetching WP Headless content). |
| **bp_post_grid** | News Grid | **[REPLACE]** | React "News Block" fetching from WP JSON API. |
| **bp_shortcodes** | Utility Widgets | **[REPLACE]** | Replaced by native React Components. |

### Deferred: Core Business (No Timeline Yet)
*Focus: Contracts, Users, Orders — Deferred until `bp_contracts` development complete.*

| Plugin | Current Role | Status | Notes |
| :--- | :--- | :--- | :--- |
| **bp_contracts** | The Everything Module | **[DEFERRED]** | Continue WordPress development. Migrate later. |
| **bp_zakazi** | Dealer Orders | **[RETIRED]** | Already replaced by `bp_contracts`. |
| **bp_booking** | Booking Numbers | **[RETIRED]** | Already replaced by `bp_contracts`. |
| **bp_points_manager** | User/Point Connection | **[RETIRED]** | Already replaced by `bp_contracts`. |
| **bp_sales** | Discounts System | **[DEFERRED]** | Part of `bp_contracts` ecosystem. |
| **gi_new_users** | Business Users | **[UNCHANGED]** | Stays in MySQL. No Laravel migration. |

---

## Proposed Changes (Detailed)

### Phase 1: The Catalog Pilot (Weeks 1-8)
**Goal**: Validate the stack with public data. Zero risk to business operations.

#### 1. Infrastructure Setup
- [ ] Initialize Laravel Project (API Only).
- [ ] Configure PostgreSQL Database (Catalog data only).
- [ ] Configure Redis for Cache/Queues.
- [ ] **Data Design**: Create Migrations for `kitchens`, `facades`, `tabletops`, `handles`.

#### 2. WordPress Sync Plugin (The Bridge)
- [ ] Create `bp_laravel_sync` plugin in WordPress.
- [ ] Implement `save_post` hooks for `bp_kitchen_add`.
- [ ] Implement "Push to API" logic:
    - [ ] Authenticate with Laravel via API Token (service-to-service).
    - [ ] Serialize Kitchen data + Images.
    - [ ] POST to `/api/v1/admin/kitchens`.
- [ ] **Verification**: Update a Kitchen in WP Admin -> Verify data appears in Postgres.

#### 3. React Frontend (Catalog)
- [ ] Initialize React App (Inertia.js or Next.js).
- [ ] Build Components:
    - [ ] `KitchenCard`
    - [ ] `KitchenFilter` (Style, Color, Material)
    - [ ] `ProductPage` (Detail view)
- [ ] **Integration**: Embed into WP pages or serve as subdomain `catalog.geosideal.by`.

---

### Phase 2: Extended Catalog (Weeks 9-14)
**Goal**: Complete all product categories in the new stack.

#### 1. Wardrobes
- [ ] Sync `bp_wardrobe_add` data to Laravel.
- [ ] Build React `WardrobeCard` / `WardrobeFilter` components.

#### 2. Related Products & Materials
- [ ] Create Laravel models for `Product`, `Material`, `Sample`.
- [ ] Sync from respective WordPress plugins.
- [ ] Build unified React product browser (if applicable).

---

### Phase 3: Dealer Resources (Weeks 15-18)
**Goal**: Migrate low-risk dealer tools.

#### 1. Dealer Files (`bp_dealer_files`) — Week 15-16
- [ ] Migrate `gi_dealers_files` metadata to Laravel.
- [ ] Move physical files to S3/MinIO or organized Laravel Storage.
- [ ] Build React "Download Center" component.
- [ ] Embed in WordPress or serve standalone.

#### 2. Salons (`bp_salons`) — Week 17-18
- [ ] Migrate `gi_salons` to Laravel `salons` table.
- [ ] Build API: `GET /salons` (with GeoJSON support).
- [ ] React Component: `StoreLocator` (using `react-leaflet` or Google Maps).
- [ ] Migrate `bp_add_salon` form to React.

---

### Phase 4: Content & Marketing (Optional / Week 19+)
**Goal**: Modernize public-facing content (low priority).

- [ ] React Landing Page (`bp_homepage` replacement).
- [ ] React News Block (fetching from WP REST API).
- [ ] Migrate shortcodes to React components.

---

### Deferred Phase: Contracts & Users (No Timeline)
**Status**: On hold until you signal readiness.

When ready, this phase will include:
- User migration (`gi_new_users` -> Laravel PostgreSQL)
- Full `bp_contracts` rebuild with Laravel Reverb
- Dealer Portal SPA
- ERP integration rewrite

---

## Laravel Helper Services (Optional Enhancements)

Even without migrating `bp_contracts`, Laravel can provide value:

| Service | Benefit |
|:--------|:--------|
| **Queue Workers** | Offload heavy tasks from WordPress (PDF generation, email sending). |
| **ERP Sync Jobs** | More reliable webhook processing via Redis queues. |
| **Notification Service** | Centralized email/push notifications. |

These are **additive** — they help `bp_contracts` without replacing it.

---

## Verification Plan

### Automated Tests (Laravel)
- **Unit Tests**:
    - Test Kitchen sync API endpoint.
    - Test data validation and transformation.
- **Feature Tests**:
    - `test_admin_can_sync_kitchen()`: Mock API request, assert Database has record.
    - `test_catalog_api_returns_correct_structure()`: Assert JSON schema.

### Manual Verification
- **Catalog Phase**:
    1. Open WP Admin -> Edit "Simon" Kitchen -> Save.
    2. Check Laravel Database -> `kitchens` table -> Row updated.
    3. Open React Catalog -> Refresh -> "Simon" shows new data.
- **Dealer Files Phase**:
    1. Access Download Center as public user.
    2. Verify files load correctly from Laravel Storage.
- **Salons Phase**:
    1. Open Store Locator page.
    2. Verify map displays with salon markers.
    3. Test search/filter functionality.

---

## Summary: What Changes vs. Original Plan

| Aspect | Original Plan | Revised (Option A) |
|:-------|:--------------|:-------------------|
| **User Migration** | Phase 2-3: Migrate to Laravel | **Deferred**: Stay in MySQL |
| **`bp_contracts`** | Phase 2: Full Rebuild | **Deferred**: Keep in WordPress |
| **`bp_dealer_files`** | Wave 4 (Month 8) | **Wave 3 (Week 15-16)** — moved earlier |
| **`bp_salons`** | Wave 4 (Month 8) | **Wave 3 (Week 17-18)** — after dealer_files |
| **Laravel Role** | Full backend replacement | **API Facade** for catalog + helper services |
| **Risk Level** | High (Big Bang migration) | **Low** (incremental, reversible) |
