import {Component, Input, Output, OnInit, EventEmitter, ViewEncapsulation} from '@angular/core';
import ResultSet from "../../Model/Search/ResultSet";
import Query from "../../Model/Search/Query";
import Result from "../../Model/Search/Result";

@Component({
    selector: 'search-results',
    styleUrls: ['./result.component.scss'],
    templateUrl: './result.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class ResultComponent implements OnInit {
    @Input()
    resultSet: ResultSet;

    @Input()
    query: Query;

    @Input()
    mode: string;

    canShow = false;
    canShowMore = false;
    canShowLess = false;
    results: Array<Result>;

    @Output()
    queryChanged: EventEmitter<Query> = new EventEmitter<Query>();

    constructor() {

    }

    showMore() {
        this.query.more();
        this.queryChanged.emit(this.query);
    }

    showLess() {
        this.query.less();
        this.queryChanged.emit(this.query);
    }

    ngOnInit() {
        this.resultSet.results.subscribe((results) => {
            this.canShowMore = results.length < this.resultSet.total;
            this.canShowLess = results.length > 10;
            this.canShow = results.length > 0;
            this.results = results;
        });
    }
}
