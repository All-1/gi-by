import { Component, type ErrorInfo, type ReactNode } from 'react';

type Props = { children: ReactNode };

type State = { error: Error | null };

export class AdminErrorBoundary extends Component<Props, State> {
  state: State = { error: null };

  static getDerivedStateFromError(error: Error): State {
    return { error };
  }

  componentDidCatch(error: Error, info: ErrorInfo): void {
    console.error('bp_knowledge_tests admin UI:', error, info.componentStack);
  }

  render() {
    if (this.state.error !== null) {
      return (
        <p className="bp-kt-error">
          Admin UI error: {this.state.error.message}
        </p>
      );
    }

    return this.props.children;
  }
}
