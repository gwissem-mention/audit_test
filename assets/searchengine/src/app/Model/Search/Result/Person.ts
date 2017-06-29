import Result from "../Result";

export default class Person extends Result {

    constructor(id: number, score: number, public firstname: string, public lastname: string, public username: string, public email: string)
    {
        super(id, score);
    }

    getType() :string
    {
        return "person";
    }

    getTitle() :string
    {
        return this.firstname + ' ' + this.lastname;
    }

    getContent() :string
    {
        return '';
    }

    getLink(): string
    {
        //@TODO Must be done other way ...
        return 'javascript:Contact_Popup.display({\'' +
            this.email +
            '\':\'' +
            this.firstname + ' ' + this.lastname +
            '\'}, window.location.href);'
        ;
    }

    getSource(): string {
        return null;
    }
}
