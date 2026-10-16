export type AdminNavSection = 'tests' | 'settings' | 'results' | 'analytics';

type Props = {
  active: AdminNavSection;
  onNavigateTests?: () => void;
  onNavigateSettings?: () => void;
  onNavigateResults?: () => void;
  onNavigateAnalytics?: () => void;
};

function navClass(active: boolean): string {
  return active ? 'current' : '';
}

export function AdminSubnav({
  active,
  onNavigateTests,
  onNavigateSettings,
  onNavigateResults,
  onNavigateAnalytics,
}: Props) {
  const urls = window.bpKnowledgeTestsAdmin?.adminUrls;

  const linkOrButton = (
    section: AdminNavSection,
    label: string,
    href: string | undefined,
    onClick?: () => void,
  ) => {
    const isActive = active === section;
    if (href) {
      return <a className={navClass(isActive)} href={href}>{label}</a>;
    }
    return (
      <button type="button" className={navClass(isActive)} onClick={onClick}>
        {label}
      </button>
    );
  };

  return (
    <nav className="bp-kt-subnav" aria-label="Knowledge tests admin">
      {linkOrButton('tests', 'All tests', urls?.list, onNavigateTests)}
      {linkOrButton('results', 'Results', urls?.results, onNavigateResults)}
      {linkOrButton('analytics', 'Analytics', urls?.analytics, onNavigateAnalytics)}
      {linkOrButton('settings', 'Settings', urls?.settings, onNavigateSettings)}
    </nav>
  );
}
