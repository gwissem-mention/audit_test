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

const SEARCH_DELAY = 300;

@Component({
    selector: 'search-engine',
    templateUrl: './search.component.html',
    styleUrls: ['./search.component.scss'],
    providers: [ SearchService ]
})
export class SearchComponent implements OnInit {
    /**
     * Primary query object
     */
    query: Query;

    /**
     * Hot query object
     */
    hotQuery: Query;

    resultSet: ResultSet;
    hotResultSet: ResultSet;

    noResutls: boolean = false;

    private hotSearchStream = new Subject<Query>();
    private searchStream = new Subject<Query>();

    /**
     * Build primary and "hot" queries and resultSets
     *
     * @param searchService
     * @param config
     */
    constructor(
        private searchService: SearchService,
        public config: Config
    ) {
        this.query = new Query(config.get('index'));
        this.hotQuery = new Query(config.get('index'));
        this.hotQuery.setItemsPerPage(5);

        this.resultSet = this.searchService.getResultSet();
        this.hotResultSet = this.searchService.getHotResultSet();
    }

    /**
     * Update hot query
     *
     * @param query
     */
    refreshHotQuery(query: Query) {
        this.hotSearchStream.next(query);
    }

    /**
     * Update primary query.
     * Update hot query with primary query terms
     *
     * @param {Query} query
     * @param {boolean} isFirstLoad
     */
    refreshQuery(query: Query, isFirstLoad: boolean = false) {
        this.searchStream.next(query);

        if (!isFirstLoad) {
            query.source = '';
        }

        this.hotQuery.setTerm(query.term);
        this.hotQuery.setSource(query.source);
        this.refreshHotQuery(this.hotQuery);
    }

    ngOnInit(): void {
        this.hotSearchStream
            .debounceTime(SEARCH_DELAY)
            .subscribe((query: Query) => {this.searchService.searchHot(query)})
        ;

        this.searchStream
            .debounceTime(SEARCH_DELAY)
            .subscribe((query: Query) => {this.searchService.search(query)})
        ;

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

        let urlQuery = queryString.parse(location.search);
        if (undefined !== urlQuery.q) {
            if (undefined !== urlQuery.source) {
                this.query.setSource(urlQuery.source);
            }
            this.query.setTerm(urlQuery.q);
            this.refreshQuery(this.query, true);
        }
    }
}
