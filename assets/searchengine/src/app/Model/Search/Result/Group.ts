import Result from "../Result";

declare let Routing: any;

export default class Group extends Result {

    constructor(id: number, score: number, public title: string, public content: string)
    {
        super(id, score);
    }

    getType() :string
    {
        return 'cdp_groups';
    }

    getTitle() :string
    {
        return this.title;
    }

    getContent() :string
    {
        return this.content;
    }

    getLink(): string {
        return Routing.generate('hopitalnumerique_communautepratique_groupe_view', {'groupe': this.id});
    }
}
