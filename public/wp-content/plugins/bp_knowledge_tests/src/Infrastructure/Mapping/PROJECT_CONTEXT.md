# Component Context: bp_knowledge_tests/src/Infrastructure/Mapping

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

| Class | Role |
|-------|------|
| `RecordMapper` | Normalizes `DBWorker` rows (objects or camelCase arrays) into domain records |

Required because `selectUni_2` may return a single unwrapped row or list shapes that are not already `Record` instances.
