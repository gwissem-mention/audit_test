import {Component, OnInit} from '@angular/core';
import './../../assets/css/searchEngine.scss';
import Query from "../Model/Search/Query";
import {SearchService} from "./search.service";

import 'rxjs/add/operator/debounceTime';
import 'rxjs/add/operator/distinctUntilChanged';
import 'rxjs/add/operator/switchMap';
import {Subject} from "rxjs/Subject";
import ResultSet from "../Model/Search/ResultSet";
import {Config} from "../app.config";

const SEARCH_DELAY = 150;

@Component({
    selector: 'search-engine',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.scss'],
    providers: [ SearchService ]
})
export class SearchComponent implements OnInit {
    query: Query;
    resultSet: ResultSet;
    hotResultSet: ResultSet;

    private searchStream = new Subject<Query>();

    constructor(
        private searchService: SearchService,
        config: Config
    ) {
        this.query = new Query(config.get('index'));
        this.resultSet = this.searchService.getResultSet();
        this.hotResultSet = this.searchService.getHotResultSet();
    }

    refreshQuery(query: Query) {
        this.searchStream.next(query);
    }

    getQuery() {
        return this.query;
    }

    getResultSet() {
        return this.resultSet;
    }

    getHotResultSet() {
        return this.hotResultSet;
    }

    getHotMode() {
        return 'lite';
    }

    ngOnInit(): void {
        this.searchStream
            .debounceTime(SEARCH_DELAY)
            .subscribe((query: Query) => {this.searchService.search(query)})
        ;
    }
}
