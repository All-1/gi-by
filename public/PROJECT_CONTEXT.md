# Project Context: GeoS Ideal - WordPress Portal

## Overview
This is a large-scale, custom WordPress installation for "GeoS Ideal" (a kitchen/furniture manufacturer). The project heavily relies on a suite of custom plugins (prefixed with `bp_`) to manage its business logic, catalogs, and dealer interactions.

> [!IMPORTANT]
> **DEVELOPMENT RULES**
> Strict development rules are in effect for this project. Before making changes, you **MUST** read and follow the guidelines in **[DEVELOPMENT_RULES.md](./DEVELOPMENT_RULES.md)**.

## Architecture
- **Platform**: WordPress.
- **Root Path**: `d:/OSPanel-6.4.0/home/dev-local.gi.by/`
- **Theme**: Looks like a custom or child theme (`wp-diary` mentioned in paths).
- **Core Logic**: Decentralized across specific custom plugins.

## Key Plugins (Custom)
Detailed architectural documentation is available in the `PROJECT_CONTEXT.md` file within each plugin's directory.

### Catalogue & Products
- **[bp_kitchen_add](./public/wp-content/plugins/bp_kitchen_add/PROJECT_CONTEXT.md)**: Monolithic Admin tool to add/edit Kitchens. Manages images and WP Pages.
- **[bp_kitchen_print](./public/wp-content/plugins/bp_kitchen_print/PROJECT_CONTEXT.md)**: Frontend catalog display with AJAX filtering.
- **[bp_wardrobe_add](./public/wp-content/plugins/bp_wardrobe_add/PROJECT_CONTEXT.md)**: Monolithic Admin tool for Wardrobes.
- **[bp_wardrobe_print](./public/wp-content/plugins/bp_wardrobe_print/PROJECT_CONTEXT.md)**: Frontend catalog for Wardrobes.
- **[bp_related_products](./public/wp-content/plugins/bp_related_products/PROJECT_CONTEXT.md)**: "Living Room" catalog.
- **[bp_facades_list](./public/wp-content/plugins/bp_facades_list/PROJECT_CONTEXT.md)**: Facade materials catalog.
- **[bp_materials](./public/wp-content/plugins/bp_materials/PROJECT_CONTEXT.md)**: Accessories and Materials manager.
- **[bp_samples](./public/wp-content/plugins/bp_samples/PROJECT_CONTEXT.md)**: Exposition samples for sale.

### Dealer & Business Logic
- **[bp_contracts](./public/wp-content/plugins/bp_contracts/PROJECT_CONTEXT.md)**: **CORE SYSTEM**. Complex MVC-style plugin with WebSockets for Contracts/Orders.
- **[bp_zakazi](./public/wp-content/plugins/bp_zakazi/PROJECT_CONTEXT.md)**: "My Orders" list for dealers, driven by per-user temporary tables.
- **[bp_points_manager](./public/wp-content/plugins/bp_points_manager/PROJECT_CONTEXT.md)**: Managing Sales Points/Managers.
- **[bp_dealer_files](./public/wp-content/plugins/bp_dealer_files/PROJECT_CONTEXT.md)**: File sharing/resources for dealers.
- **[bp_booking](./public/wp-content/plugins/bp_booking/PROJECT_CONTEXT.md)**: Reservation/Booking system.

### Marketing & Content
- **[bp_homepage](./public/wp-content/plugins/bp_homepage/PROJECT_CONTEXT.md)**: Custom homepage content builder.
- **[bp_salons](./public/wp-content/plugins/bp_salons/PROJECT_CONTEXT.md)**: "Where to buy" - Map of salons.
- **[bp_add_salon](./public/wp-content/plugins/bp_add_salon/PROJECT_CONTEXT.md)**: Submission form for new salons.
- **[bp_post_grid](./public/wp-content/plugins/bp_post_grid/PROJECT_CONTEXT.md)**: News/Blog grids.
- **[bp_shortcodes](./public/wp-content/plugins/bp_shortcodes/PROJECT_CONTEXT.md)**: Utility shortcodes (charts, contact forms).
- **[bp_sales](./public/wp-content/plugins/bp_sales/PROJECT_CONTEXT.md)**: Discount management.

### Framework & Documentation
- **[wordpress_framework](./public/wp-content/plugins/wordpress_framework/PROJECT_CONTEXT.md)**: **Core Framework**. Provides Dependency Injection Container (`ServiceContainer`) and base Workers (`DBWorker`, `KitchenWorker`) used by other plugins.
    - **Namespace**: `WPFramework`, `PersonalAccount`.
    - **Role**: Standardizes database access and dependency management.

## Technical Patterns
- **Database**: Custom tables `gi_*` are used extensively. `$wpdb` is the primary access method.
- **Frontend**: Heavy use of jQuery and classic AJAX.
- **WebSockets**: Used in `bp_contracts` for real-time collaboration.
- **Filesystem**: Direct filesystem manipulation (mkdir, unlink) in `bp_*_add` plugins for managing product images.
- **User Specific Tables**: `bp_zakazi` uses `temporary_$SUserID` tables, suggesting a distinct sync process.

## WebSocket Server
> [!IMPORTANT]
> The WebSocket server runs as a separate PHP process, not as part of standard WordPress request handling.

- **Entry Point**: `public/websocket_server.php` (outside plugin directory)
- **Port**: 8080 (hardcoded)
- **Protocol**: Custom `title::: body` message format (non-standard)
- **Dependencies**: Requires both `vendor/autoload.php` AND `wp-load.php`
- **Startup Command**: `php websocket_server.php` (requires persistent process)

### Message Protocol Examples
| Message Type | Format | Purpose |
|:-------------|:-------|:--------|
| User Registration | `idUser::: 123` | Client sends user ID on connect |
| New Contract | `newContract::: {"new_contract":"name","point_id":1}` | Create contract |
| Ping/Heartbeat | `Ping::: GetPing` | Keep-alive |
| ERP Sync | `requestUpdateOrdersUP::: 12345` | Trigger ERP sync |


## Critical Issues & remediation
> [!CAUTION]
> **Immediate Attention Required**: The following issues pose significant security and stability risks.

### 1. Unauthenticated ERP Connector (CRITICAL Security)
- **Location**: `connector_for_UP.php`
- **Risk**: Takes `logId` parameters directly from `$_GET` with **zero authentication**. Anyone can trigger ERP sync operations.
- **Hardcoded URL**: `wss://geosideal.ru/wss/` is hardcoded in file.
- **Remediation**: Add nonce verification, capability checks, or remove public access entirely.
- **Estimate**: 2-4 Hours.

### 2. Unrestricted File Uploads (Security)
- **Location**: `bp_kitchen_add`, `bp_wardrobe_add`
- **Risk**: Direct usage of `move_uploaded_file` without sufficient validation allows potential shell uploads.
- **Remediation**: Implement strict MIME-type checking and file extension allow-lists. Use `wp_handle_upload`.
- **Estimate**: 4-6 Hours per plugin.

### 3. WebSocket Input Validation (Security)
- **Location**: `bp_contracts/src/Chat.php`
- **Risk**: Many `json_decode($body)` calls without validation. No rate limiting on messages.
- **Remediation**: Add JSON schema validation, rate limiting, and sanitization.
- **Estimate**: 1-2 Days.

### 4. Monolithic Legacy Code (Maintainability)
- **Location**: `bp_kitchen_add/index.php` (60KB+), `bp_contracts`
- **Risk**: "God files" mixing logic, HTML, and SQL make debugging impossible and regression likely.
- **Remediation**: Refactor into Classes/Controllers using `wordpress_framework`.
- **Estimate**: 2-3 Weeks per major plugin (Stabilization).

### 5. WebSocket Architecture (Stability)
- **Location**: `bp_contracts`
- **Risk**: Single point of failure. Custom PHP process management is fragile on standard hosting.
- **Remediation**: Migrate to managed service (Pusher) or robust self-hosted solution (Laravel Reverb).
- **Estimate**: 1-2 Weeks (Migration).

### 6. Database Schema Lock-in (Scalability)
- **Location**: Global (`gi_*` tables)
- **Risk**: 40+ custom tables with no ORM or migration history. Hard to migrate to modern frameworks.
- **Remediation**: Document schema relationships. Create Phinx/Laravel migrations.
- **Estimate**: 1-2 Weeks (Documentation & Migrations).


## Critical Paths
1.  **Contracts & Orders**: `bp_contracts` + `bp_zakazi`.
2.  **Catalog**: `bp_kitchen_*` + `bp_wardrobe_*`.

## Estimates & Critical Analysis
> [!WARNING]
> This project is a complex bespoke application masking as a WordPress site. Standard WP development practices often do not apply.

### Component-Level Estimates

#### Tier 1: Critical / Core Systems

| Component | Role | Complexity | Refactor | Rewrite | Notes |
| :--- | :--- | :---: | :---: | :---: | :--- |
| **bp_contracts** | Personal Cabinet, WebSockets, Contracts | **Extreme** | 10-15 Days | 50-60 Days | MVC architecture, ReactPHP, Ratchet WS |
| **wordpress_framework** | DI Container, Workers | **High** | 5-7 Days | 2-3 Weeks | Foundation for all plugins |
| **bp_zakazi** | Dealer Orders Dashboard | **Medium** | 3-5 Days | 10-15 Days | `temporary_` table per-user anti-pattern |

#### Tier 2: Product Catalog (Admin + Frontend)

| Component | Role | Complexity | Refactor | Rewrite | Notes |
| :--- | :--- | :---: | :---: | :---: | :--- |
| **bp_kitchen_add** | Kitchen Admin CRUD | **High** | 1-2 Weeks | 4-6 Weeks | 60KB index.php, filesystem ops |
| **bp_kitchen_print** | Kitchen Frontend Catalog | **Medium** | 5-7 Days | 2-3 Weeks | AJAX filtering, sticky sidebar |
| **bp_wardrobe_add** | Wardrobe Admin CRUD | **Medium** | 1 Week | 3-4 Weeks | Parallel to bp_kitchen_add |
| **bp_wardrobe_print** | Wardrobe Frontend Catalog | **Medium** | 3-5 Days | 2 Weeks | 73KB index.php, code dup with kitchen |
| **bp_related_products** | Living Room Catalog | **Medium** | 3-5 Days | 2 Weeks | Links to Kitchen parent |
| **bp_facades_list** | Facade Materials Catalog | **Low-Medium** | 2-3 Days | 1 Week | Admin-focused, image management |
| **bp_materials** | Materials & Accessories | **Low** | 2-3 Days | 1 Week | Simple CRUD, lookup tables |
| **bp_samples** | Exposition Samples | **Medium** | 3-5 Days | 2 Weeks | Moderation workflow, email notifs |
| **bp_sales** | Discount Management | **Low** | 1-2 Days | 3-5 Days | Simple percentage updates |

#### Tier 3: Dealer & Business Logic

| Component | Role | Complexity | Refactor | Rewrite | Notes |
| :--- | :--- | :---: | :---: | :---: | :--- |
| **bp_points_manager** | Sales Point Assignment | **Low** | 1-2 Days | 3-5 Days | Checkbox interface, WP roles |
| **bp_dealer_files** | Dealer Resource Hub | **Medium** | 3-5 Days | 1-2 Weeks | File uploads, FAQ, textures |
| **bp_booking** | Reservation System | **Low-Medium** | 2-3 Days | 1 Week | Sequential order numbers |

#### Tier 4: Marketing & Content

| Component | Role | Complexity | Refactor | Rewrite | Notes |
| :--- | :--- | :---: | :---: | :---: | :--- |
| **bp_salons** | Salon Network / "Where to Buy" | **Medium** | 3-5 Days | 2 Weeks | Leaflet maps, multi-salon |
| **bp_add_salon** | Salon Submission Form | **Low-Medium** | 2-3 Days | 1 Week | Leaflet, image handling |
| **bp_homepage** | Homepage CMS Builder | **Medium** | 3-5 Days | 1-2 Weeks | Slider, contacts, departments |
| **bp_post_grid** | News Grids | **Low** | 1-2 Days | 3-5 Days | Country filtering, WP posts |
| **bp_shortcodes** | Utility Shortcodes | **Low** | 1-2 Days | 3-5 Days | Charts, contact forms, modals |

---

### Aggregate Project Estimates

| Scenario | Effort | Timeline | Team Size |
| :--- | :--- | :--- | :--- |
| **Stabilization Only** | 40-60 Days | 2-3 Months | 1 Senior Dev |
| **Refactor (Modernize)** | 80-120 Days | 3-5 Months | 1-2 Devs |
| **Full Rewrite (Laravel)** | 150-200 Days | 6-10 Months | 2-3 Devs |

> [!IMPORTANT]
> **Recommended Approach**: Incremental refactoring starting with `wordpress_framework` → `bp_contracts` → Catalog plugins. A full rewrite carries significant risk due to the custom database schema and WebSocket integration.

---

### Key Risks

#### 1. WebSocket Architecture (Critical)
- **Issue**: `bp_contracts` uses PHP Ratchet/ReactPHP for real-time features.
- **Impact**: Single point of failure. If WS process crashes, the entire "Personal Cabinet" stops.
- **Hosting**: Non-standard for shared WordPress hosting. Requires persistent process, supervisor, or dedicated server.
- **Mitigation**: Consider migrating to Laravel Reverb, Node.js (Socket.io), or Pusher.

#### 2. Custom Database Schema (Critical)
- **Issue**: All business data lives in `gi_*` tables (40+ custom tables), bypassing WP's post/meta system.
- **Impact**: Data migration is the **highest-risk** activity in any rewrite. No ORM, no migrations history.
- **Tables of Concern**: `gi_new_users`, `gi_new_contract`, `gi_kitchen`, `gi_salons`, `gi_booking`.
- **Mitigation**: Document schema thoroughly. Create migration scripts with rollback capability.

#### 3. Per-User Temporary Tables (High)
- **Issue**: `bp_zakazi` creates `temporary_<USER_ID>` tables for each dealer.
- **Impact**: Database bloat, maintenance nightmare, sync failures cause stale data.
- **Scale**: If 500 dealers exist, that's 500+ extra tables.
- **Mitigation**: Refactor to a single `orders` table with `user_id` index.

#### 4. Filesystem Security (High)
- **Issue**: `bp_kitchen_add`, `bp_wardrobe_add`, and others use `mkdir()`, `unlink()`, direct file writes.
- **Impact**: Path traversal, arbitrary file deletion, or upload attacks if not properly sanitized.
- **Mitigation**: Jail uploads to specific directories. Use WordPress's `wp_handle_upload()` where possible.

#### 5. Mixed Authentication (Medium-High)
- **Issue**: `bp_contracts` maintains its own `gi_new_users` table, separate from WP Users.
- **Impact**: Two separate user systems. Session hijacking risk. Password policy inconsistency.
- **Mitigation**: Audit `gi_new_users` security. Consider consolidating to WP Users with meta.

#### 6. Code Duplication (Medium)
- **Issue**: `bp_kitchen_*` and `bp_wardrobe_*` are near-identical with copy-paste code.
- **Impact**: Bug fixes must be applied twice. Divergence over time.
- **Mitigation**: Abstract shared logic into `wordpress_framework`.

#### 7. Legacy JavaScript (Medium)
- **Issue**: Heavy inline jQuery, no bundling, global variables.
- **Impact**: Hard to debug, test, or modernize. Browser console errors common.
- **Mitigation**: Incremental migration to modules, or accept as technical debt.

#### 8. Lack of Automated Tests (Medium)
- **Issue**: No visible test suite for any plugin.
- **Impact**: Refactoring is high-risk. Regression bugs likely.
- **Mitigation**: Add integration tests for critical paths (Contract creation, Order sync).

#### 9. External Sync Dependencies (Medium)
- **Issue**: `UPWorker` in `bp_contracts` syncs with external ERP system.
- **Impact**: Undocumented API. If ERP changes, sync breaks silently.
- **Mitigation**: Document ERP integration. Add monitoring/alerting for sync failures.

#### 10. Localization (Low-Medium)
- **Issue**: Mixed Russian/English in codebase, `$lang_adm` global for translations.
- **Impact**: Adding new languages is manual and error-prone.
- **Mitigation**: Migrate to WordPress i18n (`__()`, `.po` files) if multi-language support needed.

## Security
> [!IMPORTANT]
> **Strict Security Enforcement**
> A security hook script (`scripts/security_hook.php`) has been implemented to block access to sensitive files and dangerous commands. These rules are **strictly enforced** and must not be bypassed.

- **Blocked Files**:
  - `wp-config.php`
  - `.env`, `.pem`, `.key`, `id_rsa`
  - `auth.json`, `config.json` (if sensitive)
  - `db_connect.php` (Custom DB connection script)
- **Blocked Commands**:
  - `rm -rf /`
  - `sudo rm`
  - `chmod 777`
  - Writing to `/etc/`

## Recommendations
> [!TIP]
> For detailed recommendations on next steps, including testing strategy, documentation gaps, and architecture improvements, see **[RECOMMENDATIONS.md](./RECOMMENDATIONS.md)**.
