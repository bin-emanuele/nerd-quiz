import { Partecipant } from "./Partecipant";
import { Question } from "./Question";

export class Answer {
  id: number;
  partecipant_id: number;
  partecipant: Partecipant;
  question_id?: number;
  question?: Question;
  text: string;
  is_correct: boolean;
  answered_at?: Date;
  created_at: Date;
  updated_at: Date;

  constructor(data: Partial<Answer> = {}) {
    this.id = data.id || 0;
    this.partecipant_id = data.partecipant_id || 0;
    this.partecipant = new Partecipant(data.partecipant);
    this.question_id = data?.question_id;
    this.question = data.question ? new Question(data.question) : undefined;
    this.text = data.text || '';
    this.is_correct = data.is_correct || false;
    this.answered_at = data?.answered_at;
    this.created_at = data.created_at || new Date();
    this.updated_at = data.updated_at || new Date();
  }
}