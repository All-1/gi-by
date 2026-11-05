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
        <h2 id="bp-kt-critical-title">Критическое обновление</h2>
        <p>
          Сданные результаты теста <strong>{testTitle}</strong> станут недействительными, медали
          будут сняты, версия теста увеличится. Прошлые попытки останутся, но получат статус «Устарел».
        </p>
        <p className="bp-kt-modal__warn">Это действие нельзя отменить.</p>
        <div className="bp-kt-modal__actions">
          <button type="button" className="button" onClick={onCancel}>
            Отмена
          </button>
          <button type="button" className="button button-primary" onClick={onConfirm}>
            Продолжить
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
        <h2 id="bp-kt-critical-confirm-title">Подтвердите критическое обновление</h2>
        <p>Выполнить критическое обновление сейчас?</p>
        <div className="bp-kt-modal__actions">
          <button type="button" className="button" onClick={onCancel} disabled={busy}>
            Назад
          </button>
          <button
            type="button"
            className="button button-primary button-link-delete"
            onClick={onConfirm}
            disabled={busy}
          >
            {busy ? 'Обновление…' : 'Выполнить критическое обновление'}
          </button>
        </div>
      </div>
    </div>
  );
}
