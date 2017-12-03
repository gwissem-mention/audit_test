import Result from "./Result";
import Publication from "./Result/Publication";
import ForumPost from "./Result/ForumPost";
import ForumTopic from "./Result/ForumTopic";
import Person from "./Result/Person";
import Group from "./Result/Group";
import Autodiag from "./Result/Autodiag";
import CDPDiscussion from "./Result/CDPDiscussion";

export default class ResultFactory {

    static getResult(resultData: any): Result {
        let result;
        let highlight = resultData._source;
        let title = highlight.title;
        let content = highlight.content;

        if (undefined !== resultData.highlight) {
            highlight = resultData.highlight;
            if (undefined !== highlight['title.exact']) {
                title = highlight['title.exact'].join(' ');
            } else if (undefined !== highlight['title']) {
                title = highlight['title'].join(' ');
            }

            if (undefined !== highlight.content) {
                content = highlight.content.join(' ');
            }
        }

        switch (resultData._type) {
            case 'autodiag':
                return new Autodiag(resultData._source.id, 0, resultData._source.title, resultData._source.attributes);
            case "object":
                result = new Publication(
                    resultData._id,
                    resultData._score,
                    title,
                    resultData._source.alias,
                    resultData._source.synthesis
                );
                result.setSource(resultData._source.source);
                result.setContent(content);

                result.types = resultData._source.types.map((x: any) => x.libelle);

                return result;
            case "content":
                let parentData = resultData._source.parent;
                
                result = new Publication(resultData._id, resultData._score, title, resultData._source.alias, null);
                result.setContent(content);
                result.setSource(parentData.source);
                result.types = resultData._source.types.map((x: any) => x.libelle);

                let parent = new Publication(parentData.id, 0, parentData.title, parentData.alias, parentData.synthesis);
                result.setParent(parent);
                result.setCode(resultData._source.content_code);

                return result;
            case "forum_post":
                return new ForumPost(resultData._id, resultData._score, resultData._source.topic, content);
            case "forum_topic":
                return new ForumTopic(resultData._id, resultData._score, title, content, resultData._source.forumName);
            case "person":
                return new Person(resultData._id, resultData._score, resultData._source.firstname, resultData._source.lastname, resultData._source.username, resultData._source.email);
            case "cdp_groups":
                return new Group(resultData._id, resultData._score, title, content);
            case "cdp_discussion":
                return new CDPDiscussion(resultData._id, resultData._score, title, content, resultData._source.discussionId);
            default:
                console.log(resultData);
        }

        return;
    }

}
