import { Component, OnInit, Input, Output, EventEmitter } from '@angular/core';
import Query from "../../Model/Search/Query";
import Filter from "../../Model/Search/Filter";
import {Text} from "../text.service";

@Component({
    selector: 'query',
    templateUrl: './query.component.html',
    styleUrls: ['./query.component.scss']
})
export class QueryComponent implements OnInit {

    @Input()
    query: Query;

    term: string;

    timer: number;

    @Output()
    queryChanged: EventEmitter<Query> = new EventEmitter<Query>();

    constructor(protected text: Text) {}

    updateTerm(term: string): void {
        if (this.sanitizeTerm(term) !== this.query.term ) {
            this.query.setTerm(term);
            this.queryChanged.emit(this.query);
        }
    }

    removeFilter(filter: Filter): void {
        this.query.removeFilter(filter);
        this.queryChanged.emit(this.query);
    }

    ngOnInit(): void {
        this.term = this.query.term;
    }

    protected sanitizeTerm(term: string): string {
        return term.trim();
    }
}
