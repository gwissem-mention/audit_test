abstract class Result {
    readonly maxScore: number = 3;

    constructor(protected id: number, protected score: number) {
    }

    abstract getType(): string;

    abstract getTitle(): string;

    abstract getContent(): string;

    abstract getLink(): string;

    abstract getSource(): string;

    getId(): number {
        return this.id;
    }

    getRawScore(): number {
        return this.score;
    }

    getScore() {
        return Math.round(this.maxScore * (1 - (1 / Math.log((this.ponderateScore()-1) + Math.E))));
    }

    private ponderateScore() {
        return Math.exp(this.score / 2.6);
    }
}

export default Result;
