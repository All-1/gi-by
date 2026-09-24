# Rebuild Estimate & Risk Assessment

Based on the analysis of `PROJECT_CONTEXT.md` and `ARCHITECTURE_PROPOSAL.md` (V2 "Catalog First"), here is the detailed estimate and risk assessment for rebuilding the GeoS Ideal application.

## 1. Executive Summary

**Total Estimated Duration**: **6-9 Months** (Optimistic to Realistic)
**Team Composition**: 2-3 Full-stack Developers (Laravel + React experience required)

The rebuild is substantial because it involves migrating from a legacy, Monolithic WordPress plugin architecture to a modern Distributed System (Laravel API + React Frontend + PostgreSQL).

> [!IMPORTANT]
> The "Catalog First" strategy (Phase 1) significantly de-risks the project by allowing the team to validate the stack on non-critical components before tackling the complex Contracts system.

---

## 2. Detailed Estimates by Phase

### Phase 1: The Catalog ("Pilot")
**Goal**: Replace frontend catalog with React, keep WP Admin for data entry.
**Duration**: **6-8 Weeks**

| Component | Task | Estimate |
| :--- | :--- | :--- |
| **Infrastructure** | Setup Laravel, PostgreSQL, Redis, API Auth (Sanctum) | 1 Week |
| **Database** | Design & Implement Schemas (`kitchens`, `facades`, etc.) | 3-5 Days |
| **Sync Plugin** | Build WP Plugin to push `save_post` data to Laravel API | 1-2 Weeks |
| **API Development** | Endpoints for Catalog (Kitchens, Filters, Search) | 1 Week |
| **Frontend (React)** | Build "Kitchen Catalog" & "Product Page" components | 2-3 Weeks |
| **Integration** | Embed React App into WordPress Pages | 3-5 Days |
| **Testing** | Validate Data Sync and Frontend performance | 1 Week |

### Phase 2: Contracts & Business Logic ("The Core")
**Goal**: Migrate `bp_contracts` to Laravel + React.
**Duration**: **3-4 Months**

| Component | Task | Estimate |
| :--- | :--- | :--- |
| **User Migration** | Migrate `gi_new_users` to Laravel `users` (Postgres) | 1-2 Weeks |
| **Auth System** | Implement Roles (Dealer/Manager) & Policies | 1 Week |
| **Order Schema** | Refactor `bp_zakazi` temp tables to proper `orders` table | 2-3 Weeks |
| **WebSocket** | Setup Laravel Reverb, migrate Chat logic | 2-3 Weeks |
| **Contract Logic** | Re-implement complex pricing/validation rules | 4-6 Weeks |
| **Dealer Portal** | Build "Personal Cabinet" (Dashboard, Orders, Profile) | 4-5 Weeks |
| **ERP Sync** | Re-implement ERP Connector with proper security | 2 Weeks |

### Phase 3: Stabilization & Launch
**Goal**: Production readiness, data finalization.
**Duration**: **1 Month**

*   **Load Testing**: Simulate 50+ concurrent dealers.
*   **Security Audit**: Pen-test API and Auth.
*   **Documentation**: API docs for future mobile apps.
*   **Training**: Train internal staff on new stack.

---

## 3. Potential Future Issues (Risk Foresight)

### High Risks (Critical Impact)

#### 1. Data Migration Complexity (`bp_contracts` & `bp_zakazi`)
*   **Issue**: The current system uses "temporary tables" per user and non-standard `gi_*` tables without foreign keys.
*   **Risk**: Data loss or corruption during migration is highly likely. Relational integrity might be missing in the source data (e.g., orphaned orders).
*   **Mitigation**: Run "Dry Run" migrations repeatedly. Build a robust "Data Cleaner" script before migration.

#### 2. "Sync" Brittleness (Phase 1)
*   **Issue**: Pushing data from WP to Laravel via API can fail (network timeout, API down).
*   **Risk**: Desynchronization between WP Admin (what editors see) and Laravel (what customers see).
*   **Mitigation**: Implement a **Queue/Retry** mechanism in the WP Sync Plugin. Add a "Force Resync" button in WP Admin.

#### 3. Scope Creep
*   **Issue**: During a rewrite, stakeholders often ask for "small improvements" that snowball.
*   **Risk**: Timeline blows up by 50%+.
*   **Mitigation**: Strict **Feature Parity** rule. No new features until the Rebuild is live.

### Medium Risks (Operational Impact)

#### 4. SEO Challenges with React
*   **Issue**: If not implemented correctly (SSR or Pre-rendering), React SPAs can hurt SEO ranking compared to standard WP PHP pages.
*   **Risk**: Drop in organic traffic.
*   **Mitigation**: Use **Inertia.js** (SSR) or ensure the Catalog "shell" is rendered by Blade/PHP before React hydrates. Or use Next.js if SEO is paramount (adds complexity).

#### 5. Hosting Costs & Complexity
*   **Issue**: Moving from simple Shared Hosting to a setup requiring **PHP (WP) + PHP (Laravel) + Postgres + Redis + Node/Reverb (WebSockets)**.
*   **Risk**: Higher monthly costs ($50-100/mo vs $10/mo). Requires DevOps skills to maintain.
*   **Mitigation**: Use managed services (e.g., Laravel Forge, DigitalOcean App Platform) or ensuring the client understands the cost of "Modern Architecture".

#### 6. Team Skill Gap
*   **Issue**: If the current support team is only WP/PHP fluent, they will struggle to maintain the React/Laravel stack.
*   **Risk**: The project becomes "unmaintainable" by the client's internal team.
*   **Mitigation**: Include specific budget for detailed **Developer Documentation** and training sessions.

---

## 4. Recommendations

1.  **Stick to Phase 1**: Do NOT start Phase 2 until Phase 1 is in production and stable for 2 weeks. Validating the "WP -> API" link is crucial.
2.  **Use Laravel Nova or Filament (Optional)**: If WP Admin becomes too limiting for the Catalog, consider moving Admin to Laravel Filament later. But start with WP to minimize user friction.
3.  **Prioritize Tests**: Write **Feature Tests** in Laravel for the API. This is the only way to ensure the rewrite doesn't break business logic.
