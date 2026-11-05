import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './App';
import { AdminErrorBoundary } from './components/AdminErrorBoundary';
import './styles/admin.css';

function showFallbackError(message: string): void {
  const root = document.getElementById('bp-knowledge-tests-admin-root');
  if (root === null) {
    return;
  }
  let fallback = document.getElementById('bp-knowledge-tests-admin-fallback');
  if (fallback === null) {
    fallback = document.createElement('p');
    fallback.id = 'bp-knowledge-tests-admin-fallback';
    root.prepend(fallback);
  }
  fallback.textContent = message;
  fallback.classList.add('bp-kt-error');
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
  } catch (err: unknown) {
    const message = err instanceof Error ? err.message : 'Не удалось запустить админку.';
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
  const root = document.getElementById('bp-knowledge-tests-admin-root');
  if (root === null || root.querySelector('.bp-kt-page') !== null) {
    return;
  }
  showFallbackError(
    'Сборка админки не запустилась. Обновите страницу (Ctrl+F5) и проверьте консоль браузера.',
  );
}, 4000);
