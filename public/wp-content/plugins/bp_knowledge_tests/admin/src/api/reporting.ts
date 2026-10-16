import { apiRequest } from './client';
import type { Test } from './types';
import { listTests } from './catalog';

export type AttemptResultRow = {
  id: number;
  userId: number;
  userDisplayName: string | null;
  testId: number;
  testTitle: string;
  testVersion: number;
  score: number;
  status: string;
  validity: 'valid' | 'expired' | 'not_applicable';
  dateFinished: string;
  validAnswers: number;
  invalidAnswers: number;
};

export type ResultsResponse = {
  items: AttemptResultRow[];
  total: number;
  page: number;
  perPage: number;
};

export type ScoreDistribution = {
  under70: number;
  from70To79: number;
  from80To89: number;
  from90Plus: number;
};

export type TestAttemptSummary = {
  completed: number;
  passed: number;
  failed: number;
  outdated: number;
  averageScore: number;
};

export type TestAnalyticsSummary = {
  testId: number;
  title: string;
  currentVersion: number;
  questionCount: number;
  attempts: TestAttemptSummary;
  scoreDistribution: ScoreDistribution;
};

export type QuestionAnalyticsRow = {
  questionId: number;
  question: string;
  timesPresented: number;
  totalRightSelections: number;
  totalFailedSelections: number;
  missedCorrectSelections: number;
  maxCorrectOptions: number;
  averageQuestionScorePercent: number | null;
};

export type TestAnalyticsDetail = TestAnalyticsSummary & {
  questions: QuestionAnalyticsRow[];
};

export async function fetchResults(params: {
  testId?: number;
  userId?: number;
  status?: string;
  page?: number;
  perPage?: number;
}): Promise<ResultsResponse> {
  const search = new URLSearchParams();
  if (params.testId) {
    search.set('testId', String(params.testId));
  }
  if (params.userId) {
    search.set('userId', String(params.userId));
  }
  if (params.status) {
    search.set('status', params.status);
  }
  if (params.page) {
    search.set('page', String(params.page));
  }
  if (params.perPage) {
    search.set('perPage', String(params.perPage));
  }
  const query = search.toString();

  return apiRequest<ResultsResponse>(`/results${query ? `?${query}` : ''}`);
}

export async function fetchAnalyticsSummaries(): Promise<TestAnalyticsSummary[]> {
  const body = await apiRequest<{ tests: TestAnalyticsSummary[] }>('/analytics/tests');
  return body.tests;
}

export async function fetchTestAnalytics(testId: number): Promise<TestAnalyticsDetail> {
  const body = await apiRequest<{ analytics: TestAnalyticsDetail }>(`/analytics/tests/${testId}`);
  return body.analytics;
}

export { listTests };
export type { Test };
