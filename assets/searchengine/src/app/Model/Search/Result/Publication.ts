import Result from "../Result";

declare let Routing: any;

export default class Publication extends Result {

    title: string;
    alias: string;
    content: string;
    code: string;
    source: string;
    synthesis: string;
    types: string[] = [];

    children: Publication[] = [];
    parent: Publication;

    constructor(
        id: number,
        score: number,
        title: string,
        alias: string,
        synthesis: string
    ) {
        super(id, score);
        this.title = title;
        this.alias = alias;
        this.synthesis = synthesis;
    }

    setSource(source: string) {
        this.source = source;
    }

    setContent(content: string) {
        this.content = content;
    }

    setCode(code: string)
    {
        this.code = code;
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

    getTitle(): string {
        return this.code
            ? this.code + ' ' + this.title
            : this.title
        ;
    }

    getContent() :string {
        return this.content;
    }

    hasParent() : boolean {
        return Boolean(this.parent);
    }

    getLink(): string {
        if (this.parent) {
            return Routing.generate('hopital_numerique_publication_publication_contenu', {'id': this.parent.id, 'alias': this.parent.alias, 'idc': this.id, 'aliasc': this.alias});
        }

        return Routing.generate('hopital_numerique_publication_publication_objet', {'id': this.id, 'alias': this.alias});
    }
    
    getSource(): string {
        return this.source;
    }

    getSynthese(): string {
        if (null !== this.synthesis && '' !== this.synthesis) {
            return Routing.generate('hopital_numerique_publication_synthese', {'id': this.parent ? this.parent.id : this.id});
        } else {
            return null;
        }
    }
}
