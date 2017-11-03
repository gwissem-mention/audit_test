import Result from "../Result";
import Autodiag from "./Autodiag";

declare let Routing: any;

export default class AutodiagAttribute extends Result {

    parent: Autodiag;

    constructor(id: number, score: number, public title: string, public chapterId: number, public chapter: string, public chapterCode: string, protected autodiagId: number)
    {
        super(id, score);
    }

    getType(): string
    {
        return "autodiag";
    }

    getTitle(): string
    {
        return this.chapterCode + '. ' + this.chapter;
    }

    getContent(): string
    {
        return this.title;
    }

    getLink(): string {
        return Routing.generate('hopitalnumerique_autodiag_entry_add', {'autodiag': this.autodiagId})
            + '#'
            + this.chapterId
        ;
    }

    getSource(): string {
        return null;
    }

    setParent(parent: Autodiag) {
        this.parent = parent;
    }
}
