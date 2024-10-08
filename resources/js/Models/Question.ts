import { Answer } from "./Answer";
import { GameSession } from "./GameSession";
import { Partecipant } from "./Partecipant";

export class Question {
  id: number;
  text: string;
  game_session_id: number;
  game_session?: GameSession;
  booked_by_id: number | null;
  booked_by: Partecipant | null;
  answers: Answer[];
  closed_at?: Date;
  expires_at?: Date;
  created_at: Date;
  updated_at?: Date;

  constructor(data: Partial<Question> = {}) {
    this.id = data.id || 0;
    this.text = data.text || '';
    this.game_session_id = data.game_session_id || 0;
    this.game_session = data.game_session ? new GameSession(data.game_session) : undefined;
    this.booked_by_id = data.booked_by_id || null;
    this.booked_by = data.booked_by ? new Partecipant(data.booked_by) : null;
    this.answers = data.answers || [];
    this.closed_at = data?.closed_at ? new Date(data.closed_at) : undefined;
    this.expires_at = data?.expires_at ? new Date(data.expires_at) : undefined;
    this.updated_at = data?.updated_at ? new Date(data.updated_at) : undefined;
    this.created_at = data.created_at || new Date();
  }

}