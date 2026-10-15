export type Test = {
  id: number;
  title: string;
  version: number;
  achievementArea: string | null;
  dateCreation?: string;
  dateModified?: string;
};

export type Question = {
  id: number;
  testId: number;
  question: string;
  explanation: string;
  reference: string;
  dateAdded?: string;
  dateModified?: string;
};

export type Answer = {
  id: number;
  questionId: number;
  answer: string;
  isCorrect: boolean;
};

export type QuestionAnswersPayload = {
  answers: Answer[];
  correctAnswerIds: number[];
};
