import Echo from "laravel-echo";
import { PresenceChannel } from "laravel-echo/dist/channel";
import { Partecipant } from "./Partecipant";
import { Question } from "./Question";
import { Answer } from "./Answer";

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

  public online_partecipants: number[] = [];

  private echo: Echo | null = null;
  private channel: PresenceChannel | null = null;

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

  connect () {
    this.echo = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
      wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
      forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
      enabledTransports: ['ws', 'wss'],
    });

    this.channel = this.echo.join(`game-session.${this.slug}`);

    return this.channel
      .here((partecipants: Partecipant[]) => {
        console.log('Partecipants here', partecipants);

        // Add any partecipants that are not already in the list
        partecipants
          .filter(x => x.type === 'partecipant')
          .filter(x => !this.partecipants.map(y => y.id).includes(x.id))
          .forEach(p => this.partecipants.push(new Partecipant(p)));

        this.online_partecipants.splice(0, this.online_partecipants.length, ...partecipants.filter(x => x.type === 'partecipant').map((p) => p.id));
      })
      .joining((partecipant: Partecipant) => {
        if (partecipant.type !== 'partecipant') {
          return;
        }

        console.log('Partecipant joining', partecipant);

        if (!this.partecipants.map(p => p.id).includes(partecipant.id)) {
          this.partecipants.push(new Partecipant(partecipant));
        }

        this.online_partecipants.push(partecipant.id);
      })
      .leaving((partecipant: Partecipant) => {
        if (partecipant.type !== 'partecipant') {
          return;
        }

        console.log('Partecipant leaving', partecipant);

        this.online_partecipants = this.online_partecipants.filter(id => id !== partecipant.id);
      })
      .listen('GameSession\\ResetGame', ({ game_session }: { game_session: GameSession }) => {
        console.log('Writing question', game_session.status);
        this.status = game_session.status
        this.partecipants = game_session.partecipants || [];
        this.questions = game_session.questions.map(q => new Question(q))
      })
      .listen('GameSession\\WritingQuestion', ({ game_session }: { game_session: GameSession }) => {
        console.log('Writing question', game_session.status);
        this.status = game_session.status;
      })
      .listen('GameSession\\NextQuestion', ({ game_session, question }: { game_session: GameSession, question: Question }) => {
        console.log('New question', question);
        this.questions.push(new Question(question));
        this.status = game_session.status;
      })
      .listen('GameSession\\QuestionTimeout', ({ game_session, question }: { game_session: GameSession, question: Question }) => {
        console.log('Question timeout', question);
        this.questions.splice(this.questions.findIndex((q) => q.id === question.id), 1, new Question(question));
        this.status = game_session.status;
      })
      .listen('GameSession\\BookedQuestion', ({ game_session, question }: { game_session: GameSession, question: Question }) => {
        console.log('Booked question', question);
        this.questions.splice(this.questions.findIndex((q) => q.id === question.id), 1, new Question(question));
        this.status = game_session.status;
      })
      .listen('GameSession\\CheckingAnswer', ({ answer }: { answer: Answer }) => {
        console.log('Checking answer', answer);
        if (!this.currentQuestion) {
          throw new Error('No current question');
        }

        if (!this.currentQuestion.answers) {
          this.currentQuestion.answers = [];
        }
        this.currentQuestion.answers.push(answer);
        this.status = answer.question.game_session?.status || this.status;
      })
      .listen('GameSession\\AnswerResult', ({ answer, game_session }: { answer: Answer, game_session: GameSession }) => {
        console.log('Answer result', answer);
        if (!this.currentQuestion) {
          throw new Error('No current question');
        }

        const answerIndex = this.currentQuestion.answers.findIndex(a => a.id === answer.id);
        if (answerIndex !== -1) {
          this.currentQuestion.answers[answerIndex] = answer;
        } else {
          this.currentQuestion.answers.push(answer);
        }

        this.currentQuestion.closed_at = answer.question.closed_at;
        this.partecipants = game_session.partecipants;

      })
      .listen('GameSession\\GameOver', ({ game_session }: { game_session: GameSession }) => {
        console.log('Game over', game_session.status);
        this.status = game_session.status;
      })
  }

  disconnect () {
    if (this.echo) {
      this.echo.leave(`game-session.${this.slug}`);
      this.echo.disconnect();
    }
  }

  get closedQuestions () {
    return this.questions.filter(q => !!q.closed_at);
  }

  get currentQuestion () {
    const openQuestions = this.questions
      .filter(q => !q.closed_at)
      .sort((a, b) => a.created_at < b.created_at ? 1 : -1);

    if (!openQuestions?.length) {
      return null;
    }

    return openQuestions[0];
  }
}