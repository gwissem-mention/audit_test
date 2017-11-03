import Result from "./Result";
import AutodiagAttribute from "./Result/AutodiagAttribute";
import Publication from "./Result/Publication";
import ForumPost from "./Result/ForumPost";
import ForumTopic from "./Result/ForumTopic";
import Person from "./Result/Person";
import Group from "./Result/Group";
import Autodiag from "./Result/Autodiag";

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
                let chapterLabel = resultData._source.chapter_label;
                if (undefined !== resultData.highlight) {
                    chapterLabel = resultData.highlight['chapter_label.exact']
                        ? resultData.highlight['chapter_label.exact']
                        : resultData.highlight['chapter_label']
                            ? resultData.highlight['chapter_label']
                            : chapterLabel;
                }

                result = new AutodiagAttribute(resultData._id, resultData._score, title, resultData._source.chapter_id, chapterLabel, resultData._source.chapter_code, resultData._source.autodiag_id);
                let simpleParent = new Autodiag(resultData._source.autodiag_id, 0, resultData._source.autodiag_title);
                result.setParent(simpleParent);

                return result;
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
            default:
                console.log(resultData);
        }

        return;
    }

}
