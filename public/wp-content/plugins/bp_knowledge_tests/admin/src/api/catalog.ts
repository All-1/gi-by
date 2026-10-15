import { apiRequest } from './client';
import type { Answer, Question, QuestionAnswersPayload, Test } from './types';

export async function listTests(): Promise<Test[]> {
  const body = await apiRequest<{ tests: Test[] }>('/tests');
  return body.tests ?? [];
}

export async function getTest(id: number): Promise<Test> {
  const body = await apiRequest<{ test: Test }>(`/tests/${id}`);
  return body.test;
}

export async function createTest(title: string, achievementArea?: string): Promise<Test> {
  const body = await apiRequest<{ test: Test }>('/tests', {
    method: 'POST',
    body: JSON.stringify({ title, achievementArea: achievementArea ?? null }),
  });
  return body.test;
}

export async function updateTest(
  id: number,
  title: string,
  achievementArea?: string | null,
): Promise<Test> {
  const body = await apiRequest<{ test: Test }>(`/tests/${id}`, {
    method: 'PUT',
    body: JSON.stringify({ title, achievementArea: achievementArea ?? null }),
  });
  return body.test;
}

export async function deleteTest(id: number): Promise<void> {
  await apiRequest<{ deleted: boolean }>(`/tests/${id}`, { method: 'DELETE' });
}

export async function criticalUpdateTest(id: number): Promise<Test> {
  const body = await apiRequest<{ test: Test }>(`/tests/${id}/critical-update`, {
    method: 'POST',
  });
  return body.test;
}

export async function listQuestions(testId: number): Promise<Question[]> {
  const body = await apiRequest<{ questions: Question[] }>(`/tests/${testId}/questions`);
  return body.questions ?? [];
}

export async function createQuestion(
  testId: number,
  fields: Pick<Question, 'question' | 'explanation' | 'reference'>,
): Promise<Question> {
  const body = await apiRequest<{ question: Question }>(`/tests/${testId}/questions`, {
    method: 'POST',
    body: JSON.stringify(fields),
  });
  return body.question;
}

export async function updateQuestion(
  id: number,
  fields: Pick<Question, 'question' | 'explanation' | 'reference'>,
): Promise<Question> {
  const body = await apiRequest<{ question: Question }>(`/questions/${id}`, {
    method: 'PUT',
    body: JSON.stringify(fields),
  });
  return body.question;
}

export async function deleteQuestion(id: number): Promise<void> {
  await apiRequest(`/questions/${id}`, { method: 'DELETE' });
}

export async function listAnswers(questionId: number): Promise<QuestionAnswersPayload> {
  return apiRequest<QuestionAnswersPayload>(`/questions/${questionId}/answers`);
}

export async function createAnswer(
  questionId: number,
  answer: string,
  isCorrect = false,
): Promise<Answer> {
  const body = await apiRequest<{ answer: Answer }>(`/questions/${questionId}/answers`, {
    method: 'POST',
    body: JSON.stringify({ answer, isCorrect }),
  });
  return body.answer;
}

export async function updateAnswer(
  id: number,
  answer: string,
  isCorrect: boolean,
): Promise<Answer> {
  const body = await apiRequest<{ answer: Answer }>(`/answers/${id}`, {
    method: 'PUT',
    body: JSON.stringify({ answer, isCorrect }),
  });
  return body.answer;
}

export async function deleteAnswer(id: number): Promise<void> {
  await apiRequest(`/answers/${id}`, { method: 'DELETE' });
}

export async function setCorrectAnswerIds(
  questionId: number,
  answerIds: number[],
): Promise<number[]> {
  const body = await apiRequest<{ correctAnswerIds: number[] }>(
    `/questions/${questionId}/correct-answer-ids`,
    {
      method: 'PUT',
      body: JSON.stringify({ answerIds }),
    },
  );
  return body.correctAnswerIds ?? [];
}
