import { Partecipant } from "./Partecipant";
import { Question } from "./Question";

export class GameSession {
  id: number;
  title: string;
  slug: string;
  status: string;
  max_partecipants: number;
  description: string;
  partecipants: Partecipant[];
  questions: Question[];
  created_at: string;
  updated_at: string;

  constructor(data: Partial<GameSession> = {}) {
    this.id = data.id || 0;
    this.title = data.title || '';
    this.slug = data.slug || '';
    this.status = data.status || '';
    this.max_partecipants = data.max_partecipants || 0;
    this.description = data.description || '';
    this.partecipants = data.partecipants?.map(p => new Partecipant(p)) || [];
    this.questions = data.questions?.map(q => new Question(q)) || [];
    this.created_at = data.created_at || '';
    this.updated_at = data.updated_at || '';
  }

}