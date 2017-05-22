import Result from "./Result";
import Autodiag from "./Result/Autodiag";
import Publication from "./Result/Publication";
import ForumPost from "./Result/ForumPost";
import ForumTopic from "./Result/ForumTopic";
import Person from "./Result/Person";

export default class ResultFactory {

    static getResult(resultData: any): Result {
        let result;
        let highlight = resultData._source;
        let title = highlight.title;
        let content = highlight.content;

        if (undefined !== resultData.highlight) {
            highlight = resultData.highlight;
            if (undefined !== highlight.title) {
                title = highlight.title.join(' ');
            }

            if (undefined !== highlight.content) {
                content = highlight.content.join(' ');
            }
        }

        switch (resultData._type) {
            case 'autodiag':
                return new Autodiag(resultData._id, resultData._score, title, resultData._source.chapter_label, resultData._source.autodiag_id);
            case "object":
                result = new Publication(resultData._id, resultData._score, title, resultData._source.alias, content);
                result.types = resultData._source.types.map((x: any) => x.libelle);

                return result;
            case "content":
                let parentData = resultData._source.parent;
                result = new Publication(resultData._id, resultData._score, title, resultData._source.alias,  content);
                result.types = resultData._source.types.map((x: any) => x.libelle);
                let parent = new Publication(parentData.id, 0, parentData.title, parentData.alias);
                result.setParent(parent);

                return result;
            case "forum_post":
                return new ForumPost(resultData._id, resultData._score, resultData._source.topic, content);
            case "forum_topic":
                return new ForumTopic(resultData._id, resultData._score, title, resultData._source.forumName);
            case "person":
                return new Person(resultData._id, resultData._score, resultData._source.firstname, resultData._source.lastname, resultData._source.username);
            default:
                console.log(resultData);
        }

        return;
    }

}
