import { Partecipant } from "./Partecipant";

export class GameSession {
  id: number;
  title: string;
  slug: string;
  max_partecipants: number;
  description: string;
  partecipants: Partecipant[];
  //questions: Question[];
  created_at: string;
  updated_at: string;

  constructor(data: Partial<GameSession> = {}) {
    this.id = data.id || 0;
    this.title = data.title || '';
    this.slug = data.slug || '';
    this.max_partecipants = data.max_partecipants || 0;
    this.description = data.description || '';
    this.partecipants = data.partecipants || [];
    //this.questions = data.questions || [];
    this.created_at = data.created_at || '';
    this.updated_at = data.updated_at || '';
  }

}