import Result from "../Result";

declare let Routing: any;

export default class Autodiag extends Result {

    constructor(id: number, score: number, public title: string, public chapterId: number, public chapter: string, protected autodiagId: number)
    {
        super(id, score);
    }

    getType() :string
    {
        return "Autodiagnostic";
    }

    getTitle() :string
    {
        return this.chapter;
    }

    getContent() :string
    {
        return this.title;
    }

    getLink(): string {
        return Routing.generate('hopitalnumerique_autodiag_entry_add', {'autodiag': this.autodiagId})
            + '#'
            + this.chapterId
        ;
    }
}
