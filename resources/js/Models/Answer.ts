import { Partecipant } from "./Partecipant";
import { Question } from "./Question";

export interface Answer {
  id: number;
  partecipant_id: number;
  partecipant: Partecipant;
  question_id: number;
  question: Question;
  text: string;
  is_correct: boolean;
  answered_at?: Date;
  created_at: Date;
  updated_at: Date;
}