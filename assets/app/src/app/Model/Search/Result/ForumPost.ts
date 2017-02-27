import Result from "../Result";

declare let Routing: any;

export default class ForumPost extends Result {

    constructor(id: number, score: number, protected topic: any, protected content: string)
    {
        super(id, score);
    }

    getType() :string
    {
        return "Forum post";
    }

    getTitle() :string
    {
        return this.topic.title;
    }

    getContent() :string
    {
        return this.content;
    }

    getLink(): string {
        return Routing.generate('ccdn_forum_user_topic_show', {'forumName': this.topic.forumName, 'topicId': this.topic.id});
    }
}
