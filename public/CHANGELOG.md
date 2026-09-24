# Project Changelog

This document tracks changes to the project. See **[DEVELOPMENT_RULES.md](./DEVELOPMENT_RULES.md#9-change-logging)** for the mandatory entry format.

---

## Log

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
