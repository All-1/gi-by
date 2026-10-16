type Props = {
  active: 'tests' | 'settings';
  onNavigateTests?: () => void;
  onNavigateSettings?: () => void;
};

export function AdminSubnav({ active, onNavigateTests, onNavigateSettings }: Props) {
  const urls = window.bpKnowledgeTestsAdmin?.adminUrls;
  const testsHref = urls?.list;
  const settingsHref = urls?.settings;

  const testsClass = active === 'tests' ? 'current' : '';
  const settingsClass = active === 'settings' ? 'current' : '';

  return (
    <nav className="bp-kt-subnav" aria-label="Knowledge tests admin">
      {testsHref ? (
        <a className={testsClass} href={testsHref}>All tests</a>
      ) : (
        <button type="button" className={testsClass} onClick={onNavigateTests}>All tests</button>
      )}
      {settingsHref ? (
        <a className={settingsClass} href={settingsHref}>Settings</a>
      ) : (
        <button type="button" className={settingsClass} onClick={onNavigateSettings}>
          Settings
        </button>
      )}
    </nav>
  );
}
