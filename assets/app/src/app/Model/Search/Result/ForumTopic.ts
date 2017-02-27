import Result from "../Result";

declare let Routing: any;

export default class ForumTopic extends Result {

    constructor(id: number, score: number, protected title: string, protected forumName: string) {
        super(id, score);
    }

    getType() :string {
        return "Forum topic";
    }

    getTitle() :string {
        return this.title;
    }

    getContent() :string {
        return null;
    }

    getLink(): string {
        return Routing.generate('ccdn_forum_user_topic_show', {'forumName': this.forumName, 'topicId': this.id});
    }
}
