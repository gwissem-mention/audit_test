import Result from "../Result";

declare let Routing: any;

export default class Publication extends Result {

    title: string;
    alias: string;
    content: string;
    types: string[] = [];

    children: Publication[] = [];
    parent: Publication;

    constructor(
        id: number,
        score: number,
        title: string,
        alias: string,
        content: string = null
    ) {
        super(id, score);
        this.title = title;
        this.alias = alias;
        this.content = content;
    }

    addType(type: string)
    {
        this.types.push(type);
    }

    setParent(parent: Publication)
    {
        this.parent = parent;
    }

    getType(): string {
        return this.types.join(', ');
    }

    getTypesToString(): string {
        return this.types.join(', ');
    }

    getTitle(): string {
        return this.title;
    }

    getContent() :string {
        return this.content;
    }

    getLink(): string {
        if (this.parent) {
            return Routing.generate('hopital_numerique_publication_publication_contenu', {'id': this.parent.id, 'alias': this.parent.alias, 'idc': this.id, 'aliasc': this.alias});
        }

        return Routing.generate('hopital_numerique_publication_publication_objet', {'id': this.id, 'alias': this.alias});
    }
}
