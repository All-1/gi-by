# Architecture Proposal V2: "Catalog First" Strategy

## Executive Summary
Based on your feedback, we will adopt a **"Catalog First"** strategy. This allows us to validate the new **Laravel (Logic) + API + React (View)** stack on public-facing content before migrating the mission-critical Contracts/Orders system.

**The Workflow:**
1.  **Logic & Data**: Managed in **Laravel** (Postgres).
2.  **API Layer**: Laravel exposes secure REST/JSON APIs.
3.  **Consumption**:
    -   **React.js**: Renders the dynamic catalog (Post Rendering) for the frontend.
    -   **WordPress Plugin**: Acts as a lightweight consumer (if needed) to embed React apps into existing WP pages or fetch basic SEO strings.

---

## Phase 1: The Catalog ("Pilot" Project)
*Targeting: `bp_kitchen_add`, `bp_kitchen_print`, `bp_facades_list`*

### 1. The "WP Sync" Strategy (WP Admin -> Laravel API)
To strictly follow the rule that **Catalog Management stays in WordPress**, we will implement a "Push" architecture.

-   **Workflow**:
    1.  **Admin Action**: The Manager creates/edits a Kitchen in the standard WordPress Admin panel (using standard WP fields or ACF).
    2.  **Sync Event**: On `save_post` or a custom button click, a custom WordPress Plugin gathers this data.
    3.  **API Push**: The Plugin sends a secure `POST /api/v1/kitchens` request to the **Laravel API**.
    4.  **Storage**: Laravel validates and saves the data into **PostgreSQL**.
    5.  **Frontend**: The React Frontend fetches the clean structure directly from **Laravel** (fast, relational), ensuring the frontend is decoupled from WP's database structure.

-   **Benefit**: Editors keep their familiar WP interface. Developers get a clean, structured Relational Database in Laravel to build the complex frontend features on.

### 2. User Organization (Simplified)
Since Catalog and Contracts "do not interfere":
-   **Catalog Phase**: We don't need to touch Users yet. Admins use WP. The Public interacts with the React Catalog (no login required).
-   **Contracts Phase (Later)**: We will solve the Dealer Login separation then.

### 3. Database & Logic (Laravel)
-   **New Models**: `Kitchen`, `Facade`, `Tabletop`, `Handle`.
-   **Schema**: Optimized for performance and filtering (e.g., JSON columns for flexible attributes).

### 4. Frontend (The "New" `bp_kitchen_print`)
-   **React App**: Embedded in a WP Page.
-   **Source**: Fetches from `api.geosideal.by`.

---

## Phase 2: Contracts & Business Logic (The "Core")
*Once the Catalog stack is proven, we migrate `bp_contracts`.*

-   **Auth**: Laravel takes over User Management (creating the "Single Source of Truth").
-   **WebSockets**: Migrate `Chat.php` logic to **Laravel Reverb**.
-   **Dealer Portal**: A pure React SPA (Single Page Application) for the "Personal Cabinet", communicating strictly with Laravel.

---

## Detailed Component Migration Plan (Phase 1)

| Current Component | Role | New Strategy |
| :--- | :--- | :--- |
| **bp_kitchen_add** | Admin: Add/Edit Kitchens | **Keep** in WP Admin + **Sync Plugin** to push to Laravel API. |
| **bp_kitchen_print** | Frontend: Display Catalog | **Replace** with React Component (embedded in WP). |
| **bp_facades_list** | Catalog: Materials | **Merge** into Laravel "Materials" Module (Sync from WP). |
| **Data Storage** | `gi_kitchen`, `/public/catalogue/` | **Sync** to Postgres table & Laravel Storage. |

---

## Resolved Decisions
1.  **Single Entry Point**: We will strictly use WordPress Admin for all management.
2.  **Data Flow**: WordPress is the "Source of Truth" for content editing. Laravel API is the "Sync Target" and "Frontend Source".
3.  **Isolation**: Catalog and Contracts are treated as separate systems initially.

---

## Phase 3: User Identity (Dual System Strategy)
*Requirement: PostgreSQL must be used. Laravel handles Business Users.*

We will implement a **clean separation** of user management:

| System | Database | Users | Purpose |
|:-------|:---------|:------|:--------|
| **WordPress** | MySQL | `wp_users` | Catalog Admins, Content Editors |
| **Laravel** | **Postgres** | `users` | Dealers, Distributors, Managers |

### 1. Migration (`gi_new_users` -> Laravel)
All current "Business Users" in `gi_new_users` will be migrated to Laravel's Postgres `users` table.
-   **One-Time Script**: Reads from MySQL, writes to Postgres.
-   **Password**: Re-hash or create a custom Laravel `HashDriver` to support the legacy hash.

### 2. Authentication Flows

#### Flow A: Admin (Catalog)
1.  Admin goes to `/wp-admin`.
2.  WordPress authenticates against `wp_users` (MySQL).
3.  Admin edits catalog, WP Plugin syncs to Laravel API.

#### Flow B: Business User (Contracts)
1.  Dealer/Distributor/Manager opens React App (`/cabinet`).
2.  React sends `POST /api/login` to **Laravel**.
3.  Laravel authenticates against Postgres `users`.
4.  Laravel returns a **Sanctum API Token**.
5.  React uses this Token for all subsequent `/api/contracts` requests.

### 3. Role-Based Access (Laravel)
```php
// User.php Model
protected $casts = ['role' => RoleEnum::class];

// RoleEnum.php
enum RoleEnum: string {
    case Dealer = 'dealer';
    case Distributor = 'distributor';
    case Manager = 'manager';
}
```

### 4. Authorization (Policies)
```php
// ContractPolicy.php
public function view(User $user, Contract $contract) {
    // Dealer sees own contracts. Manager sees all.
    return $user->id === $contract->user_id 
        || $user->role === RoleEnum::Manager;
}
```

**Benefit**: Complete separation of concerns. WordPress remains simple (content). Laravel becomes the secure business logic engine with proper Postgres experience.
