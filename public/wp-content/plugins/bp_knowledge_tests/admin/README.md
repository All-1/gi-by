# Knowledge tests — admin React app

Build output: `../assets/admin/app.js` (enqueued by `[knowledge_tests_admin]` shortcode).

```bash
cd admin
npm ci --prefix ../../../../vendor
npm run build
```

Dependencies install into `public/vendor/node_modules`.

Mount: add a personal-account page with shortcode `knowledge_tests_admin`. Requires capability `bp_manage_knowledge_tests_catalog` (factory `administrator` / `sales_manager` roles on activate).
