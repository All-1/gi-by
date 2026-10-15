import { useState } from 'react';
import { AllTestsPage } from './pages/AllTestsPage';
import { TestEditorPage } from './pages/TestEditorPage';

type View = { kind: 'list' } | { kind: 'edit'; testId: number | null };

function readInitialView(): View {
  const root = document.getElementById('bp-knowledge-tests-admin-root');
  const initial = root?.dataset.initialView ?? 'list';
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

function navigateToList(): void {
  const url = window.bpKnowledgeTestsAdmin?.adminUrls?.list;
  if (url) {
    window.location.assign(url);
    return;
  }
}

export function App() {
  const [view, setView] = useState<View>(readInitialView);

  const goList = () => {
    if (view.kind === 'edit' && window.bpKnowledgeTestsAdmin?.adminUrls?.list) {
      navigateToList();
      return;
    }
    setView({ kind: 'list' });
  };

  if (view.kind === 'list') {
    return (
      <AllTestsPage
        onAddTest={() => setView({ kind: 'edit', testId: null })}
        onEditTest={(id) => setView({ kind: 'edit', testId: id })}
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
