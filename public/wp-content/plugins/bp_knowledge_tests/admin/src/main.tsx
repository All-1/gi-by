import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './App';
import { AdminErrorBoundary } from './components/AdminErrorBoundary';
import './styles/admin.css';

function hideFallback(): void {
  document.getElementById('bp-knowledge-tests-admin-fallback')?.remove();
}

function showFallbackError(message: string): void {
  const fallback = document.getElementById('bp-knowledge-tests-admin-fallback');
  if (fallback !== null) {
    fallback.textContent = message;
    fallback.classList.add('bp-kt-error');
  }
}

function mount(): void {
  const rootEl = document.getElementById('bp-knowledge-tests-admin-root');
  if (rootEl === null) {
    console.error('bp_knowledge_tests: mount node #bp-knowledge-tests-admin-root not found.');
    return;
  }

  try {
    createRoot(rootEl).render(
      <StrictMode>
        <AdminErrorBoundary>
          <App />
        </AdminErrorBoundary>
      </StrictMode>,
    );
    hideFallback();
  } catch (err: unknown) {
    const message = err instanceof Error ? err.message : 'Admin UI failed to start.';
    showFallbackError(message);
  }
}

function scheduleMount(): void {
  if (document.getElementById('bp-knowledge-tests-admin-root') !== null) {
    mount();
    return;
  }
  document.addEventListener('DOMContentLoaded', mount);
}

scheduleMount();

window.setTimeout(() => {
  const fallback = document.getElementById('bp-knowledge-tests-admin-fallback');
  const root = document.getElementById('bp-knowledge-tests-admin-root');
  if (fallback === null || root === null) {
    return;
  }
  if (root.querySelector('.bp-kt-page') !== null) {
    return;
  }
  fallback.textContent =
    'The React admin bundle did not start. Hard-refresh (Ctrl+F5) and check the browser console for app.js.';
}, 4000);
