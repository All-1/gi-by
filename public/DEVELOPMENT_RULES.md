# Development Rules

**Version**: 2.0 (adapted from giby-2.0) | **Last Updated**: 2026-09-24 | **Scope**: GeoS Ideal WordPress portal (`public/`)

---

> [!IMPORTANT]
> **PRIMARY DIRECTIVE**: Read [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) before any changes. It maps plugins (`bp_*`), the WebSocket personal account (`bp_contracts`), critical security issues, and links to per-component `PROJECT_CONTEXT.md` files.

---

## Quick Reference

| Section | Purpose | Key Rules |
|---------|---------|-----------|
| **Security** | Protected files, commands, uploads | §1.1–1.3 |
| **Architecture** | Framework, DB, legacy, `bp_contracts` | §2.1–2.5 |
| **GRASP** | Design patterns (9 patterns) | §3.1–3.9 |
| **SOLID** | Object-oriented principles | §4 |
| **Composition** | Composition vs inheritance/traits | §5.1–5.5 |
| **Documentation** | `PROJECT_CONTEXT` hierarchy, links | §6.1–6.4 |
| **Workflow** | FS safety, doc sync | §7 |
| **Enforcement** | Quality gates, debt | §8 |
| **Change log** | `CHANGELOG.md` | §9 |
| **Debugging** | Changelog-first investigation | §10 |
| **Code size** | Method/class limits (new code) | §11 |

---

## 1. Security (Strictly Enforced)

> [!CAUTION]
> `security_hook.php` **BLOCKS** restricted access. Attempts are logged and rejected.

### 1.1 Restricted Files
**MUST NOT** read, write, or modify:

- `wp-config.php`, `db_connect.php`
- `.env`, `.pem`, `.key`, `id_rsa`
- `.ssh/*`, `.aws/*`

### 1.2 Forbidden Commands
**MUST NOT** execute:

- `rm -rf /` or broad recursive deletions
- `sudo`, `chmod 777`
- Writing to `/etc/` or other system directories

### 1.3 Safe File Uploads
- **NEVER** use raw `move_uploaded_file` without validation
- **ALWAYS** use `wp_handle_upload` or strict whitelist (`.jpg`, `.png`, `.pdf` only)
- **NEVER** allow `.php`, `.sh`, `.exe`, or other executables

### 1.4 High-Risk Endpoints (Document Before Touching)
Known issues are listed in [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md). **Do not weaken** auth on:

- `bp_contracts/connector_for_UP.php` (unauthenticated ERP trigger)
- `bp_contracts/sender/*.php` (legacy mail endpoints)
- WebSocket input in `bp_contracts/src/Chat.php` (validate and rate-limit when extending)

---

## 2. Architecture & Code Standards

### 2.1 Subsystems & Patterns

| Subsystem | Location | Pattern |
|-----------|----------|---------|
| **Catalog / admin plugins** | `wp-content/plugins/bp_kitchen_*`, `bp_wardrobe_*`, … | Legacy `index.php` + gradual extraction |
| **Shared framework** | `wp-content/plugins/wordpress_framework` | `ServiceContainer`, base Workers (`DBWorker`, …), namespaces `WPFramework` / shared utilities |
| **Personal account (core)** | `wp-content/plugins/bp_contracts` | `PersonalAccount\Core\SystemConstructor` + `Container`, Workers, `InteractionInterface`, WebSocket `Chat.php` |
| **Real-time** | `public/websocket_server.php` | Separate PHP process; not a normal WP request |

**New features**

- Prefer classes in `wordpress_framework` or plugin `src/` namespaces over new procedural blocks in giant `index.php` files.
- In **bp_contracts**, route behavior through `InteractionInterface` / user traits / Workers — not new branches in `Chat.php` without documenting WS titles in `bp_contracts/src/PROJECT_CONTEXT.md`.

### 2.2 Database Integrity
- **NO NEW TABLES** without documenting them in the relevant plugin (or site) `PROJECT_CONTEXT.md`.
- **FORBIDDEN for new work**: per-user temporary tables (e.g. `bp_zakazi` `temporary_$userId`). **Do not add new plugins that copy this pattern.** New data uses unified tables + `user_id` (or domain keys).
- **SQL**: Use `$wpdb->prepare()` or `DBWorker` / framework Workers. No string-concatenated user input in SQL.
- **ERP / PDO**: `bp_contracts` `UPWorker` uses PDO via `db_connect.php` — treat as sensitive; never expose connection details in docs or commits.

### 2.3 Legacy Code Handling
- **Containment**: Do not grow monoliths (`bp_kitchen_add/index.php`, `bp_contracts/js/modal_window.js`, etc.) with unrelated logic.
- **Extract**: New logic → framework class or plugin `src/` class; legacy file only wires hooks/shortcodes/WS titles.
- **bp_contracts**: Existing trait-heavy user model is legacy; new cross-cutting behavior should prefer injected Workers/Services when practical (see §5).

### 2.4 Code Duplication
- **DRY**: `bp_kitchen_*` and `bp_wardrobe_*` share large duplicated surfaces.
- **Do not** copy-paste between product plugins.
- **Do** abstract into `wordpress_framework` (services/traits only where §5 allows).

### 2.5 Known Legacy Exceptions (Do Not Extend)
Documented in PROJECT_CONTEXT; fix only with an explicit plan:

| Pattern | Where | Rule |
|---------|-------|------|
| Per-user temp tables | `bp_zakazi` | No new features on temp tables |
| Custom auth table | `gi_new_users` vs WP users | Do not add a third user store |
| Unbundled global JS | `bp_contracts/js/*` | New UI: isolate globals; document in `js/PROJECT_CONTEXT.md` |

---

## 3. GRASP Design Patterns

**Target**: all **new** and **refactored** code. Legacy files are not required to be rewritten in one pass.

### Pattern Quick Reference

| Pattern | Rule | Local example |
|---------|------|----------------|
| **Information Expert** | Logic on the class that holds the data | Order visibility on `UserMain` / role class, not raw SQL in `Chat.php` |
| **Creator** | A creates B if A aggregates B | `Factory::createDependentObjects` for dialogs under a contract |
| **Controller** | Thin coordination of a use case | `InteractionInterface` delegates to `UserControler` / COR |
| **Low Coupling** | Inject dependencies | Register services in `SystemConstructor` or `ServiceContainer` |
| **High Cohesion** | One main purpose per class | `InvoiceWorker` vs mixing mail + invoices in one class |
| **Polymorphism** | Type-based behavior via subclasses | `Dealer` / `FactoryWorker` / `Admin` instead of giant `if ($role)` |
| **Pure Fabrication** | Technical artifacts | `DBWorker`, `AnalyticsWorker`, DTO/capsule traits for shape only |
| **Indirection** | Mediate between layers | Workers between controllers and `$wpdb` |
| **Protected Variations** | Stable interface at variation points | `DealerInterface`, ERP sync behind `UPWorker` |

### 3.1 Information Expert

```php
// ❌ WRONG: Chat.php encodes business rules
// ✅ CORRECT: User role or Worker has data + rules
// e.g. ManageAnalytics::getAnalytics() + AnalyticsWorker, not ad hoc SQL in Chat
```

### 3.2 Creator

```php
// ✅ CORRECT: Factory builds dialog graph for a contract
// Factory::createDependentObjects → DialogOrder, DialogConsultation, …
```

### 3.3 Controller

```php
// ✅ CORRECT: InteractionInterface::regNewMessage → UserControler → COR → NotificationWorker
// ❌ WRONG: 200-line method in InteractionInterface that touches $wpdb directly
```

### 3.4–3.9
Apply the same intent as giby-2.0: favor injected Workers, role polymorphism, and interfaces at ERP/sync/notification boundaries. See [RECOMMENDATIONS.md](./RECOMMENDATIONS.md) for staged refactors.

---

## 4. SOLID Principles

| Principle | Rule | Checklist |
|-----------|------|-----------|
| **SRP** | One reason to change | Can you describe the class in one sentence? |
| **OCP** | Extend without editing core | New role = new user class/trait bundle, not new `switch` in five files |
| **LSP** | Subtypes honor base contracts | Role classes respect `UserMain` / interfaces |
| **ISP** | Small interfaces | `OrdersInterface` vs one mega-interface |
| **DIP** | Depend on abstractions | Container-injected Workers, not `new DBWorker()` in random methods |

---

## 5. Composition Over Inheritance (New Code)

### 5.1 Primary Rule
**Prefer** constructor-injected services/Workers over deep inheritance and new traits.

**Legacy note**: `bp_contracts` already composes many **capability traits** (`WorkWithContracts`, `ManageAnalytics`, …). Do not add large new traits; prefer a Worker + thin delegation from the user class.

### 5.2 Trait Usage (Strict for New Code)

New traits **only** if **all** apply:

| Condition | Requirement |
|-----------|-------------|
| **Frequency** | Shared across many call sites OR single plugin domain (e.g. existing `bp_contracts` user traits) |
| **Size** | Small helpers; no growing “god traits” |
| **State** | Avoid new stateful traits; prefer User/Worker properties |
| **Documentation** | `PROJECT_CONTEXT.md` explains why trait exists |

**Do not** copy `ManageContractors`-scale traits into other plugins without a decomposition plan.

### 5.3 Abstract Base Classes
Acceptable for **related domain types** (e.g. product create flows shared by kitchen/wardrobe) using template-method **only** when 3+ siblings share the same algorithm. Prefer framework services when &lt;3 types.

### 5.4 Decision Tree
Same as giby-2.0: unrelated sharing → service class; related product family → abstract strategy; otherwise duplicate until a third variant appears.

### 5.5 PR Checklist
- [ ] New logic in a class/Worker, not another 500-line `index.php` block
- [ ] Dependencies registered in container/bootstrap
- [ ] No new inheritance depth &gt;2 for new types
- [ ] Traits only with justification in `PROJECT_CONTEXT.md`

---

## 6. Documentation Architecture

### 6.1 Hierarchy (This Repository)

| Level | Location | Content |
|-------|----------|---------|
| **Site** | `public/PROJECT_CONTEXT.md` | Portal map, WebSocket, cross-plugin issues |
| **Rules** | `public/DEVELOPMENT_RULES.md` | This file |
| **Strategy** | `public/ARCHITECTURE_PROPOSAL.md`, `IMPLEMENTATION_PLAN.md`, `REBUILD_ESTIMATE_AND_RISKS.md` | Roadmap & estimates (not duplicated in plugins) |
| **Component** | `wp-content/plugins/<plugin>/PROJECT_CONTEXT.md` | Plugin-specific design |
| **Nested** | e.g. `bp_contracts/src/Workers/Analytics/PROJECT_CONTEXT.md` | Deep dives |

**Rules**

- One **canonical** home for detail; parents **link** down, children **link** up to site or plugin root.
- If you add a folder with substantial logic and no `PROJECT_CONTEXT.md`, **create** it and add a link from the parent `PROJECT_CONTEXT.md`.
- **Do not** paste full WebSocket command lists in both site and plugin root — canonical list: [bp_contracts/src/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/src/PROJECT_CONTEXT.md).

### 6.2 Refactoring / Moving Docs
1. `grep` all references to the old path
2. Update `public/PROJECT_CONTEXT.md` and affected plugin `PROJECT_CONTEXT.md` files
3. Verify relative links from nested files (`../../../PROJECT_CONTEXT.md` from `plugins/bp_contracts/…`)
4. Delete old file only when zero references remain
5. Log structural moves in [CHANGELOG.md](./CHANGELOG.md)

### 6.3 Link Conventions

| From | To site root doc |
|------|------------------|
| `public/` | `./PROJECT_CONTEXT.md` |
| `public/wp-content/plugins/bp_contracts/` | `../../../PROJECT_CONTEXT.md` |
| `public/wp-content/plugins/bp_contracts/src/Workers/` | `../../../../PROJECT_CONTEXT.md` |

**Prohibited**: absolute paths (`d:\…`), machine-specific OSPanel paths in committed docs.

### 6.4 Deduplication
- Integration **estimates** → `REBUILD_ESTIMATE_AND_RISKS.md`
- **Strategy** → `ARCHITECTURE_PROPOSAL.md`
- **Plugin behavior** → that plugin’s `PROJECT_CONTEXT.md`
- When changing code, update the **lowest** relevant `PROJECT_CONTEXT.md` in the same change (or immediately after).

---

## 7. Workflow & File Operations

- **Documentation sync**: Logic change → update matching `PROJECT_CONTEXT.md` (§6).
- **File system**: Prefer `WP_Filesystem` over raw `mkdir` / `unlink` / `file_put_contents` where WP APIs exist.
- **WebSocket server**: After protocol or bootstrap changes, update `bp_contracts/src/PROJECT_CONTEXT.md` and note restart requirement in CHANGELOG.
- **Composer / vendor**: Do not commit secrets; document new PHP dependencies in plugin `PROJECT_CONTEXT` or CHANGELOG.

---

## 8. Enforcement & Quality Gates

### 8.1 Pull Request Checklist
- [ ] Read relevant `PROJECT_CONTEXT.md` sections
- [ ] Security §1 not violated
- [ ] New tables / WS commands / shortcodes documented
- [ ] No broken relative links in edited markdown
- [ ] Significant work logged in `CHANGELOG.md`

### 8.2 Technical Debt

| Category | Severity | Action |
|----------|----------|--------|
| Broken doc links | High | Fix before merge |
| New undocumented WS titles | High | Add to `src/PROJECT_CONTEXT.md` |
| Duplicate long prose across PROJECT_CONTEXT files | Medium | Deduplicate next touch |
| Legacy file growth without extraction | Medium | Track in CHANGELOG / RECOMMENDATIONS |

Record ongoing debt in [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) (Critical Issues / remediation) or [RECOMMENDATIONS.md](./RECOMMENDATIONS.md).

---

## 9. Change Logging

**Mandatory** for significant changes in [CHANGELOG.md](./CHANGELOG.md):

```markdown
### [YYYY-MM-DD] Short Title
**Author**: Name
**Logic**:
- WHY this change was made
- Problem → Solution
- Trade-offs
**Changes**:
- `path/to/file.php`: Description
- `path/to/new_file.php`: [NEW] Purpose
```

---

## 10. Debugging Workflow

1. **Date filter**: `CHANGELOG.md` entries before the bug was seen
2. **Component filter**: Match paths to plugin/`bp_contracts` area
3. **Logic review**: Read **Logic** sections for bad assumptions
4. **WebSocket issues**: Compare client `handler*` names with server `Title:::` in `Chat.php` and `src/PROJECT_CONTEXT.md`

---

## 11. Code Size Limits (New & Refactored Code)

Legacy monoliths are exempt until intentionally refactored; **do not** make them larger without a decomposition ticket.

### 11.1 Methods
- **Max ~15 lines** (excluding blanks/comments), except: `__construct`, config arrays, thin delegators that only forward to one Worker.

### 11.2 Classes
- **Max ~120 lines** for new classes (abstract bases up to ~200).
- **bp_contracts** existing classes may exceed this; extracting a Worker is preferred over adding another 100 lines.

### 11.3 Enforcement
- &gt;50% over limit without plan → reject or split before merge
- Track deliberate exceptions in CHANGELOG **Logic**

---

## Related Documentation

| Document | Purpose |
|----------|---------|
| [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) | Site architecture & plugin index |
| [ARCHITECTURE_PROPOSAL.md](./ARCHITECTURE_PROPOSAL.md) | Rebuild / modernization strategy |
| [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) | Roadmap |
| [REBUILD_ESTIMATE_AND_RISKS.md](./REBUILD_ESTIMATE_AND_RISKS.md) | Effort & risk |
| [RECOMMENDATIONS.md](./RECOMMENDATIONS.md) | Improvement backlog |
| [CHANGELOG.md](./CHANGELOG.md) | Change history |
| [bp_contracts/PROJECT_CONTEXT.md](./wp-content/plugins/bp_contracts/PROJECT_CONTEXT.md) | Personal account & WebSocket core |
