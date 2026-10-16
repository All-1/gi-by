import { useState } from 'react';
import { AllTestsPage } from './pages/AllTestsPage';
import { AnalyticsPage } from './pages/AnalyticsPage';
import { ResultsPage } from './pages/ResultsPage';
import { SettingsPage } from './pages/SettingsPage';
import { TestEditorPage } from './pages/TestEditorPage';

type View =
  | { kind: 'list' }
  | { kind: 'settings' }
  | { kind: 'results' }
  | { kind: 'analytics' }
  | { kind: 'edit'; testId: number | null };

function readInitialView(): View {
  const root = document.getElementById('bp-knowledge-tests-admin-root');
  const initial = root?.dataset.initialView ?? 'list';
  if (initial === 'settings') {
    return { kind: 'settings' };
  }
  if (initial === 'results') {
    return { kind: 'results' };
  }
  if (initial === 'analytics') {
    return { kind: 'analytics' };
  }
  if (initial === 'edit-new') {
    return { kind: 'edit', testId: null };
  }
  if (initial.startsWith('edit-')) {
    const id = Number.parseInt(initial.slice('edit-'.length), 10);
    if (!Number.isNaN(id) && id > 0) {
      return { kind: 'edit', testId: id };
    }
  }
  return { kind: 'list' };
}

function navigateToAdminPage(key: 'list' | 'settings' | 'results' | 'analytics'): void {
  const url = window.bpKnowledgeTestsAdmin?.adminUrls?.[key];
  if (url) {
    window.location.assign(url);
  }
}

export function App() {
  const [view, setView] = useState<View>(readInitialView);

  const goList = () => {
    if (view.kind === 'edit' && window.bpKnowledgeTestsAdmin?.adminUrls?.list) {
      navigateToAdminPage('list');
      return;
    }
    setView({ kind: 'list' });
  };

  const goSettings = () => {
    if (window.bpKnowledgeTestsAdmin?.adminUrls?.settings) {
      navigateToAdminPage('settings');
      return;
    }
    setView({ kind: 'settings' });
  };

  const goResults = () => {
    if (window.bpKnowledgeTestsAdmin?.adminUrls?.results) {
      navigateToAdminPage('results');
      return;
    }
    setView({ kind: 'results' });
  };

  const goAnalytics = () => {
    if (window.bpKnowledgeTestsAdmin?.adminUrls?.analytics) {
      navigateToAdminPage('analytics');
      return;
    }
    setView({ kind: 'analytics' });
  };

  if (view.kind === 'settings') {
    return (
      <SettingsPage
        onNavigateTests={goList}
        onNavigateResults={goResults}
        onNavigateAnalytics={goAnalytics}
      />
    );
  }

  if (view.kind === 'results') {
    return (
      <ResultsPage
        onNavigateTests={goList}
        onNavigateSettings={goSettings}
        onNavigateAnalytics={goAnalytics}
      />
    );
  }

  if (view.kind === 'analytics') {
    return (
      <AnalyticsPage
        onNavigateTests={goList}
        onNavigateSettings={goSettings}
        onNavigateResults={goResults}
      />
    );
  }

  if (view.kind === 'list') {
    return (
      <AllTestsPage
        onAddTest={() => setView({ kind: 'edit', testId: null })}
        onEditTest={(id) => setView({ kind: 'edit', testId: id })}
        onOpenSettings={goSettings}
        onOpenResults={goResults}
        onOpenAnalytics={goAnalytics}
      />
    );
  }

  return (
    <TestEditorPage
      testId={view.testId}
      onBack={goList}
      onCreated={(id) => setView({ kind: 'edit', testId: id })}
    />
  );
}
