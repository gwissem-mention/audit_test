import Result from "../Result";

declare let Routing: any;

export default class Autodiag extends Result {

    constructor(id: number, score: number, public title: string) {
        super(id, score);
    }

    getType(): string
    {
        return null;
    }

    getTitle(): string
    {
        return this.title;
    }

    getContent(): string
    {
        return null;
    }

    getLink() : string
    {
        return Routing.generate('hopitalnumerique_autodiag_entry_add', {'autodiag': this.id});
    }

    getSource(): string
    {
        return null;
    }

}