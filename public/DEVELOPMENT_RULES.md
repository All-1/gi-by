# Strict Development Rules & Guidelines

> [!IMPORTANT]
> **PRIMARY DIRECTIVE: READ THE PROJECT CONTEXT FIRST**
> The [PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) file is the **single most important document** in this repository. 
> *   You **MUST** read and understand it before making any changes.
> *   It contains the architectural map, critical issues, and component outlines.
> *   Ignoring the context file will lead to breaking changes and is strictly forbidden.

## 1. Security (Strictly Enforced)

> [!CAUTION]
> A security hook (`security_hook.php`) actively monitors and **BLOCKS** the following actions. Attempts to bypass these restrictions will be logged and rejected.

### 1.1 Restricted Files
You **MUST NOT** attempt to read, write, or modify the following sensitive files:
*   `wp-config.php`
*   `db_connect.php` (Custom database connection)
*   `.env`, `.pem`, `.key`, `id_rsa`
*   Any file within `.ssh/` or `.aws/` directories

### 1.2 Forbidden Commands
You **MUST NOT** execute destructive or high-privilege commands:
*   `rm -rf /` or any broad recursive deletion
*   `sudo` usage
*   `chmod 777` (Universal execute permissions)
*   Writing to system directories like `/etc/`

### 1.3 Safe File Uploads
*   **NEVER** implement file upload logic using raw `move_uploaded_file` without validation.
*   **ALWAYS** use `wp_handle_upload` or enforce strict whitelist validation (allow ONLY `.jpg`, `.png`, `.pdf`).
*   **NEVER** allow upload of `.php`, `.sh`, `.exe`, or other executable extensions.

## 2. Architecture & Code Standards

### 2.1 Framework Usage
*   **DO NOT** write raw PHP scripts or immediate procedural code for new features.
*   **MUST USE** the `wordpress_framework` plugin patterns:
    *   Use the **ServiceContainer** for dependencies.
    *   Extend base **Workers** (`DBWorker`, `KitchenWorker`) for business logic.

### 2.2 Database Integrity
*   **NO NEW TABLES** without explicit documentation in `PROJECT_CONTEXT.md` and approval.
*   **FORBIDDEN**: Creating "Temporary Per-User Tables" (e.g., `temporary_123_cart`). This is a legacy anti-pattern that causes massive bloat. Use a unified table with a `user_id` column instead.
*   **NO RAW SQL** for simple CRUD if a Worker/Model exists. If writing SQL, you **MUST** use `$wpdb->prepare()`.

### 2.3 Legacy Code Handling
*   **Rule of Containment**: When editing massive legacy files (e.g., `bp_kitchen_add/index.php`), **DO NOT** add more spaghetti code.
*   **Refactor Strategy**: Extract new logic into value-objects, classes, or helper functions within the `wordpress_framework` namespace, and call them from the legacy file.

### 2.4 Code Duplication
*   **DRY Principle**: There is significant duplication between `bp_kitchen_*` and `bp_wardrobe_*` plugins.
*   **Prohibition**: **DO NOT** copy-paste code from one to the other.
*   **Solution**: Abstract shared logic into `wordpress_framework` (Traits or Services) and reuse it.

### 2.5 SOLID Principles
*   **Adherence**: All new classes and refactored code **MUST** adhere to SOLID principles.
*   **Single Responsibility**: Classes should have one reason to change. Avoid "God Classes".
*   **Open/Closed**: Entities should be open for extension but closed for modification. Use interfaces and dependency injection.
*   **Liskov Substitution**: Subtypes must be substitutable for their base types.
*   **Interface Segregation**: Clients should not be forced to depend on interfaces they do not use.
*   **Dependency Inversion**: Depend on abstractions, not concretions. Use the `ServiceContainer`.

## 3. Workflow & File Operations

*   **Documentation Sync**: If you modify the logic of a plugin, you **MUST** update its specific `PROJECT_CONTEXT.md` immediately.
*   **File System Safety**: Avoid direct `mkdir()`, `unlink()`, or `file_put_contents()` where possible. Use WordPress `WP_Filesystem` API to ensure permission compatibility across environments.

## 4. Enforcement

Violation of these rules, especially regarding Security and Database patterns, will result in immediate rejection of code changes. These rules exist to prevent the collapse of this complex, bespoke architecture.

## 5. Change Logging
*   **Mandatory Log**: All significant architectural changes, refactors, or new features **MUST** be logged in `public/CHANGELOG.md`.
*   **Format**: Use the following markdown template for every entry:

```markdown
### [YYYY-MM-DD] Short Title
**Author**: Name
**Logic**: 
- Detailed explanation of WHY this change was made.
- What was the problem? What is the solution?
- Any critical decisions or trade-offs?
**Changes**:
- `path/to/file.php`: Description of modification (e.g., "Refactored `process()` to use ServiceContainer").
- `path/to/new_file.php`: [NEW] Description of purpose.
```

*   **Traceability**: This replaces standard commit messages for high-level tracking.

## 6. Debugging Workflow
When a bug is reported, use the changelog as the first line of investigation:
*   **Step 1: Date Filter**: Identify when the bug was first observed and review all `CHANGELOG.md` entries *before* that date.
*   **Step 2: Component Filter**: Cross-reference the bug's affected component (file/plugin) with the `**Changes**` sections of entries.
*   **Step 3: Logic Review**: Read the `**Logic**` sections of suspect entries to understand the reasoning. This often reveals flawed assumptions or unintended side-effects.
*   **Benefit**: This is a lightweight `git bisect` with human-readable context, helping trace root causes faster.

