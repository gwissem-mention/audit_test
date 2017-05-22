import Result from "../Result";
export default class Person extends Result {

    constructor(id: number, score: number, public firstname: string, public lastname: string, public username: string)
    {
        super(id, score);
    }

    getType() :string
    {
        return "Personne";
    }

    getTitle() :string
    {
        return this.firstname + ' ' + this.lastname;
    }

    getContent() :string
    {
        return '';
    }

    getLink(): string {
        return;
    }
}
