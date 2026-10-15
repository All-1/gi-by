type Props = {
  open: boolean;
  testTitle: string;
  onCancel: () => void;
  onConfirm: () => void;
};

export function CriticalUpdateDialog({ open, testTitle, onCancel, onConfirm }: Props) {
  if (!open) {
    return null;
  }

  return (
    <div className="bp-kt-modal-backdrop" role="presentation">
      <div className="bp-kt-modal" role="dialog" aria-labelledby="bp-kt-critical-title">
        <h2 id="bp-kt-critical-title">Critical update</h2>
        <p>
          This will invalidate passed results for <strong>{testTitle}</strong>, remove
          achievements, and bump the test version. Historical attempts remain but become outdated.
        </p>
        <p className="bp-kt-modal__warn">This action cannot be undone.</p>
        <div className="bp-kt-modal__actions">
          <button type="button" className="button" onClick={onCancel}>
            Cancel
          </button>
          <button type="button" className="button button-primary" onClick={onConfirm}>
            Continue
          </button>
        </div>
      </div>
    </div>
  );
}

type ConfirmProps = {
  open: boolean;
  onCancel: () => void;
  onConfirm: () => void;
  busy: boolean;
};

export function CriticalUpdateConfirmDialog({
  open,
  onCancel,
  onConfirm,
  busy,
}: ConfirmProps) {
  if (!open) {
    return null;
  }

  return (
    <div className="bp-kt-modal-backdrop" role="presentation">
      <div className="bp-kt-modal" role="dialog" aria-labelledby="bp-kt-critical-confirm-title">
        <h2 id="bp-kt-critical-confirm-title">Confirm critical update</h2>
        <p>Run the critical update now?</p>
        <div className="bp-kt-modal__actions">
          <button type="button" className="button" onClick={onCancel} disabled={busy}>
            Go back
          </button>
          <button
            type="button"
            className="button button-primary button-link-delete"
            onClick={onConfirm}
            disabled={busy}
          >
            {busy ? 'Updating…' : 'Execute critical update'}
          </button>
        </div>
      </div>
    </div>
  );
}
