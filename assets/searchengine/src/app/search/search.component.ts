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
import {Observable} from "rxjs/Observable";

let queryString = require("query-string");

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
    noResutls: boolean = false;

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

        let urlQuery = queryString.parse(location.search);
        if (undefined !== urlQuery.q) {
            this.query.setTerm(urlQuery.q);
            this.refreshQuery(this.query);
        }

        /**
         * Empty result observable
         */
        let hasResultsObservable = Observable.create((observer: any) => {
            let searching = false;
            let count = 0;
            let iteration = 0;

            let init = (query: Query) => {
                searching = !query.isEmpty();
                count = 0;
                iteration = 0;
            };

            let complete = (resultsCount: number) => {
                iteration++;
                count += resultsCount;

                if (iteration === 2) {
                    observer.next(!searching || count > 0);
                }
            };

            this.searchStream.subscribe(query => init(query));
            this.resultSet.results.subscribe(results => complete(results.length));
            this.hotResultSet.results.subscribe(results => complete(results.length));
        });

        hasResultsObservable.subscribe((hasResults: boolean) => this.noResutls = !hasResults);
    }
}
