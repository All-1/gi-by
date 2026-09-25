# Project Changelog

This document tracks changes to the project. Entry format is defined in **Cursor User Rules** (CHANGELOG section); global rules are not duplicated in [DEVELOPMENT_RULES.md](./DEVELOPMENT_RULES.md).

---

## Log

### [2026-09-25] Site context: link to DEVELOPMENT_RULES
**Author**: AI Assistant
**Logic**:
- Restore navigation from the portal map to project rules without duplicating rule text in PROJECT_CONTEXT.
**Changes**:
- `public/PROJECT_CONTEXT.md`: Related docs line with link to DEVELOPMENT_RULES.md.

### [2026-09-25] Development Rules v2.4 (project-only file)
**Author**: AI Assistant
**Logic**:
- Global rules live only in Cursor Settings; remove Part A and duplicate pointers (CHANGELOG/debugging/code size) from the repo file.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: WordPress / GeoS Ideal sections only (§0–§5); renumbered §3–§5 doc/workflow/debug.
- `public/CHANGELOG.md`: Point format to User Rules.

### [2026-09-24] Development Rules v2.3 (correct Part A / Part B split)
**Author**: AI Assistant
**Logic**:
- Part A = existing universal rules (GRASP, SOLID, composition, docs, CHANGELOG, debugging, code size, security baseline); Part B = WordPress / GeoS Ideal only. Removed abbreviated G1–G8-only Part A from v2.2.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Restructured v2.3; GRASP/SOLID/§9/§10/§11 moved to Part A only.

### [2026-09-24] Development Rules v2.2 (Part A / Part B split)
**Author**: AI Assistant
**Logic**:
- Separate **global** rules (Cursor Settings → Rules) from **GeoS Ideal project** rules in one file to avoid duplicating Part B in Settings; Part A includes G8 activation to read Part B + PROJECT_CONTEXT.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Part A (copy block G1–G8) + Part B (§0–§11 project rules).

### [2026-09-24] Development Rules v2.1 (clarity & precedence)
**Author**: AI Assistant
**Logic**:
- Remove ambiguity around canonical sources (this file vs PROJECT_CONTEXT vs Cursor User Rules), conflict precedence, restricted-file edge cases, CHANGELOG “significant”, PHP line counting, legacy containment, and when tests are expected.
- Keep security and architecture intent from v2.0; align trait/base-class gates with enforceable thresholds.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Rewritten to v2.1 (§0 precedence, §1.1–1.2 clarifications, §9 significance table, §11 counting/tiers, §8.3 tests).

### [2026-09-24] Site context: single source for development rules
**Author**: AI Assistant
**Logic**:
- Keep strict development rules in `DEVELOPMENT_RULES.md` (and Cursor User Rules) only; avoid duplicating the mandatory-read callout in the portal map.
**Changes**:
- `public/PROJECT_CONTEXT.md`: Removed DEVELOPMENT_RULES block from Overview.

### [2026-09-24] Knowledge tests implementation plan
**Author**: AI Assistant
**Logic**:
- Consolidate architecture, phases, DB summary, bp_contracts integration, and QA into one engineering document next to the product spec.
- Add plugin PROJECT_CONTEXT index and site plugin list entry for discoverability.
**Changes**:
- `public/wp-content/plugins/bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: [NEW] Full implementation plan and project context.
- `public/wp-content/plugins/bp_knowledge_tests/PROJECT_CONTEXT.md`: [NEW] Plugin doc index linking spec + plan.
- `public/PROJECT_CONTEXT.md`: Link to bp_knowledge_tests.

### [2026-09-24] Development Rules v2.0 (giby-2.0 adaptation)
**Author**: AI Assistant
**Logic**:
- Align site rules with giby-2.0 `DEVELOPMENT_RULES.md` (GRASP, composition, documentation hierarchy, code size, enforcement) while scoping to this WordPress-only repo (`public/`).
- Document `bp_contracts`, WebSocket, and `PROJECT_CONTEXT` nesting; remove Laravel-only references; add pragmatic legacy exceptions (`bp_zakazi`, traits).
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Rewritten to v2.0 structure with local paths and related-doc index.

### [2026-01-15] Implemented Strict Development Rules
**Author**: AI Assistant
**Logic**: 
- Validated project security requirements (`security_hook.php`) and architectural patterns (`wordpress_framework`).
- Established a single source of truth for rules to prevent legacy anti-patterns (e.g., per-user tables, god files).
- Created a mechanism to track code duplication issues between `bp_kitchen` and `bp_wardrobe`.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: [NEW] Created file with strict rules for Security, Architecture, Workflow, and Code Duplication.
- `public/PROJECT_CONTEXT.md`: Added "Primary Directive" alert linking to the new Rules file.

### [2026-01-16] Unified Changelog Format Definition
**Author**: AI Assistant
**Logic**: 
- Moved the Changelog format definition from `CHANGELOG.md` to `DEVELOPMENT_RULES.md`.
- Ensures a single source of truth for the format and reduces redundancy.
- Prevents the template in the changelog file from becoming outdated.
**Changes**:
- `public/CHANGELOG.md`: Removed "Format" section, replaced with link to Rules.
- `public/DEVELOPMENT_RULES.md`: Added "Format" definition to Section 5.

### [2026-01-16] Added SOLID Principles
**Author**: AI Assistant
**Logic**: 
- User requested explicit inclusion of SOLID principles to ensure code quality and prevent future complaints/issues.
- Strengthens the "Architecture & Code Standards" section.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Added Section 2.5 "SOLID Principles".

### [2026-01-16] Added Debugging Workflow
**Author**: AI Assistant
**Logic**: 
- User suggested using the Changelog as a debugging tool for root cause analysis.
- Formalizes a process to trace bugs by date and component using the **Changes** and **Logic** sections.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Added Section 6 "Debugging Workflow".
