import Result from "../Result";

declare let Routing: any;

export default class Autodiag extends Result {

    constructor(id: number, score: number, public title: string, public attributes: any[]) {
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
        return this.attributes.map(function (attribute: any) {
            return attribute.label;
        }).slice(0, 3).join('<br />');
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
