import { GameSession } from "./GameSession";

export class Partecipant {
  id: number;
  type: 'host' | 'partecipant';
  name: string;
  game_session_slug: string;
  game_session_id: number;
  game_session: GameSession;
  answers_available: number;
  answers_correct: number;
  last_answered_at: string;
  created_at: string;
  updated_at: string;

  constructor(data: Partial<Partecipant> = {}) {
    this.id = data?.id || 0;
    this.type = data?.type || 'partecipant';
    this.name = data?.name || '';
    this.game_session_slug = data?.game_session_slug || '';
    this.game_session_id = data?.game_session_id || 0;
    this.game_session = data?.game_session || new GameSession();
    this.answers_available = data?.answers_available || 0;
    this.answers_correct = data?.answers_correct || 0;
    this.last_answered_at = data?.last_answered_at || '';
    this.created_at = data?.created_at || '';
    this.updated_at = data?.updated_at || '';
  }
}