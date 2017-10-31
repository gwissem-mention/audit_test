import Result from "../Result";

declare let Routing: any;

export default class CDPDiscussion extends Result {

    constructor(id: number, score: number, public title: string, public content: string, protected discussionId: number)
    {
        super(id, score);
    }

    getType() :string
    {
        return 'cdp_discussion';
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
        return Routing.generate('hopitalnumerique_communautepratique_discussions_public_desfult_discussion', {'discussion': this.discussionId});
    }

    getSource(): string {
        return null;
    }
}
