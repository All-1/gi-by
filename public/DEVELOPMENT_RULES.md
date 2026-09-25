# Development Rules (GeoS Ideal WordPress `public/`)

**Version**: 2.4 | **Last Updated**: 2026-09-25

Global rules — security baseline, precedence, GRASP, SOLID, composition, documentation principles, CHANGELOG format, debugging workflow, code size limits, git/PR conduct — live in **Cursor Settings → Rules** only. **This file is not a copy of Settings**; it adds WordPress and this repository.

| Document | Role |
|----------|------|
| **Cursor User Rules** | All cross-project rules (see Settings) |
| **This file** | `public/` portal: `bp_*`, `gi_*`, WebSocket, legacy, PROJECT_CONTEXT paths |
| **[PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md)** | Site map and risks — not rule text |
| **[CHANGELOG.md](./CHANGELOG.md)** | Significant changes (format in User Rules) |

---

## 0. Before you change code here

1. Follow **User Rules** (including reading this file and relevant `PROJECT_CONTEXT.md` before edits under `public/`).
2. Apply global GRASP, SOLID, composition, and code-size rules to new/refactored code here.
3. Follow **§1–§2** and **§7** in this file.

| § | Topic (this repo) |
|---|-------------------|
| **§1** | Uploads, `security_hook`, high-risk endpoints |
| **§2** | Plugins, framework, DB, legacy |
| **§3** | PROJECT_CONTEXT hierarchy |
| **§4** | WP workflow, WebSocket server |
| **§5** | WS debugging addendum |

---

## 1. Security (WordPress / this repo)

> [!CAUTION]
> `scripts/security_hook.php` **blocks** restricted access. Attempts are logged and rejected.

Global restricted files and forbidden commands apply via User Rules. Additionally:

### 1.1 Safe file uploads

- **NEVER** use raw `move_uploaded_file` without validation.
- **ALWAYS** use `wp_handle_upload` or a strict whitelist (e.g. `.jpg`, `.png`, `.pdf` only).
- **NEVER** allow `.php`, `.sh`, `.exe`, or other executables as uploads.

### 1.2 High-risk endpoints (document before touching)

Known issues are in [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md). **Do not weaken** auth on:

- `bp_contracts/connector_for_UP.php` (unauthenticated ERP trigger)
- `bp_contracts/sender/*.php` (legacy mail endpoints)
- WebSocket input in `bp_contracts/src/Chat.php` (validate and rate-limit when extending)

---

## 2. Architecture and code standards

### 2.1 Subsystems and patterns

| Subsystem | Location | Pattern |
|-----------|----------|---------|
| **Catalog / admin plugins** | `wp-content/plugins/bp_kitchen_*`, `bp_wardrobe_*`, … | Legacy `index.php` + gradual extraction |
| **Shared framework** | `wp-content/plugins/wordpress_framework` | `ServiceContainer`, Workers, `WPFramework` / shared utilities |
| **Personal account (core)** | `wp-content/plugins/bp_contracts` | `SystemConstructor` + `Container`, Workers, `InteractionInterface`, WebSocket `Chat.php` |
| **Real-time** | `public/websocket_server.php` | Separate PHP process; not a normal WP request |

**New features**

- Prefer classes in `wordpress_framework` or plugin `src/` over new procedural blocks in giant `index.php` files.
- In **bp_contracts**, route behavior through `InteractionInterface` / user traits / Workers — not new branches in `Chat.php` without documenting WS titles in [bp_contracts/src/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/src/PROJECT_CONTEXT.md).

### 2.2 Database integrity

- **NO NEW TABLES** without documenting columns and purpose in the relevant plugin (or site) `PROJECT_CONTEXT.md`.
- **Schema changes** (`ALTER TABLE`, new indexes on `gi_*`) — document in the same change (or immediately after) in that plugin’s `PROJECT_CONTEXT.md`.
- **FORBIDDEN for new work**: per-user temporary tables (e.g. `bp_zakazi` `temporary_$userId`). New data uses unified tables + `user_id` (or domain keys).
- **SQL**: `$wpdb->prepare()` or framework Workers. No concatenated user input in SQL.
- **ERP / PDO**: `UPWorker` uses PDO via `db_connect.php` — treat as sensitive; never expose connection details in docs or commits.

### 2.3 Legacy code handling

| Rule | Meaning |
|------|---------|
| **Containment** | Do not add **new business logic** (≈30+ lines) to monoliths (`bp_kitchen_add/index.php`, huge `modal_window.js`, etc.) without an extraction plan in CHANGELOG or ticket. |
| **Extract** | New logic → framework or plugin `src/` class; legacy file only wires hooks/shortcodes/WS titles. |
| **bp_contracts** | Trait-heavy user model is legacy; new cross-cutting behavior → injected Workers/Services when practical. |

### 2.4 Code duplication

- **DRY**: `bp_kitchen_*` and `bp_wardrobe_*` share large duplicated surfaces.
- **Do not** copy-paste between product plugins.
- **Do** abstract into `wordpress_framework` (services; new traits only per User Rules).

### 2.5 Known legacy exceptions (do not extend)

| Pattern | Where | Rule |
|---------|-------|------|
| Per-user temp tables | `bp_zakazi` | No new features on temp tables |
| Custom auth table | `gi_new_users` vs WP users | Do not add a third user store |
| Unbundled global JS | `bp_contracts/js/*` | New UI: isolate globals; document in [js/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/js/PROJECT_CONTEXT.md) |

### 2.6 Design patterns (local examples)

Apply global GRASP/SOLID from User Rules using these illustrations:

| Pattern | Local example |
|---------|----------------|
| Information Expert | Analytics on `ManageAnalytics` + `AnalyticsWorker`, not ad hoc SQL in `Chat.php` |
| Controller | `InteractionInterface` → `UserControler` / COR |
| Low Coupling | Register in `SystemConstructor` or `ServiceContainer` |
| Polymorphism | `Dealer` / `FactoryWorker` / `Admin` vs giant `if ($role)` |
| Protected Variations | ERP sync behind `UPWorker` |

### 2.7 Composition (this codebase)

- Existing **bp_contracts** capability traits (`WorkWithContracts`, …) stay; do not add large new traits — prefer Worker + thin delegation.
- **Do not** copy `ManageContractors`-scale traits into other plugins without a decomposition plan.

See [RECOMMENDATIONS.md](./RECOMMENDATIONS.md) for staged refactors.

---

## 3. Documentation architecture (this repository)

Global documentation principles are in User Rules. **Paths and canonical files here**:

| Level | Location | Content |
|-------|----------|---------|
| **Site** | [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) | Portal map, WebSocket, cross-plugin issues, remediation |
| **Project rules** | This file | WordPress / GeoS Ideal only |
| **Strategy** | [ARCHITECTURE_PROPOSAL.md](./ARCHITECTURE_PROPOSAL.md), [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md), [REBUILD_ESTIMATE_AND_RISKS.md](./REBUILD_ESTIMATE_AND_RISKS.md) | Roadmap and estimates (not duplicated in plugins) |
| **Component** | `wp-content/plugins/<plugin>/PROJECT_CONTEXT.md` | Plugin-specific design |
| **Nested** | e.g. `bp_contracts/src/Workers/Analytics/PROJECT_CONTEXT.md` | Deep dives |
| **Product spec** | e.g. `bp_knowledge_tests/spec/*.md` | Requirements and engineering plans |

**Rules**

- Site **PROJECT_CONTEXT** does **not** duplicate User Rules or this file.
- Full WebSocket command list: **[bp_contracts/src/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/src/PROJECT_CONTEXT.md)** only — not duplicated at site root.

### 3.1 Link conventions (from nested plugin paths)

| From | To site root doc |
|------|------------------|
| `public/` | `./PROJECT_CONTEXT.md` |
| `public/wp-content/plugins/bp_contracts/` | `../../../PROJECT_CONTEXT.md` |
| `public/wp-content/plugins/bp_contracts/src/Workers/` | `../../../../PROJECT_CONTEXT.md` |

### 3.2 Deduplication (this repo)

- **Estimates** → [REBUILD_ESTIMATE_AND_RISKS.md](./REBUILD_ESTIMATE_AND_RISKS.md)
- **Strategy** → [ARCHITECTURE_PROPOSAL.md](./ARCHITECTURE_PROPOSAL.md)
- **Plugin behavior** → that plugin’s `PROJECT_CONTEXT.md`

Record ongoing debt in [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) (Critical Issues) or [RECOMMENDATIONS.md](./RECOMMENDATIONS.md).

---

## 4. Workflow and file operations (WordPress)

- **Documentation sync**: Logic change → matching `PROJECT_CONTEXT.md` (§3).
- **File system**: Prefer `WP_Filesystem` over raw `mkdir` / `unlink` / `file_put_contents` where WP APIs exist.
- **WebSocket server**: After protocol or bootstrap changes, update [bp_contracts/src/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/src/PROJECT_CONTEXT.md) and note **restart** in [CHANGELOG.md](./CHANGELOG.md).
- **Composer / vendor**: Do not commit secrets; document new PHP dependencies in plugin `PROJECT_CONTEXT` or CHANGELOG.

### 4.1 Pull request checklist (this repo)

- [ ] §1–§2 respected
- [ ] New tables / WS commands / shortcodes documented in PROJECT_CONTEXT
- [ ] `public/CHANGELOG.md` updated when significant (User Rules CHANGELOG section)
- [ ] Global PR checklist (User Rules) satisfied

---

## 5. Debugging (WebSocket / personal account)

Use the global debugging workflow (User Rules) with [CHANGELOG.md](./CHANGELOG.md).

**WebSocket addendum**: Compare client `handler*` names with server `Title:::` in `Chat.php` and [src/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/src/PROJECT_CONTEXT.md).

**Code size**: Global §11 in User Rules applies to **PHP classes under `public/`**.

---

## Related documentation

| Document | Purpose |
|----------|---------|
| [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) | Site architecture and plugin index |
| [ARCHITECTURE_PROPOSAL.md](./ARCHITECTURE_PROPOSAL.md) | Modernization strategy |
| [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) | Roadmap |
| [REBUILD_ESTIMATE_AND_RISKS.md](./REBUILD_ESTIMATE_AND_RISKS.md) | Effort and risk |
| [RECOMMENDATIONS.md](./RECOMMENDATIONS.md) | Improvement backlog |
| [CHANGELOG.md](./CHANGELOG.md) | Change history |
| [bp_contracts/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/PROJECT_CONTEXT.md) | Personal account and WebSocket core |
