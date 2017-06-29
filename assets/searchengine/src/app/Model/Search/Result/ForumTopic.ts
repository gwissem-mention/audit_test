import Result from "../Result";

declare let Routing: any;

export default class ForumTopic extends Result {

    constructor(id: number, score: number, protected title: string, protected content: string, protected forumName: string) {
        super(id, score);
    }

    getType() :string {
        return "forum_topic";
    }

    getTitle() :string {
        return this.title;
    }

    getContent() :string {
        return this.content;
    }

    getLink(): string {
        return Routing.generate('ccdn_forum_user_topic_show', {'forumName': this.forumName, 'topicId': this.id});
    }

    getSource(): string {
        return null;
    }
}
