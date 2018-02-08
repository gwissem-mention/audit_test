import { Injectable } from '@angular/core';
import { Http, RequestOptions, URLSearchParams } from '@angular/http';

import { Observable }     from 'rxjs/Observable';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/switchMap';

import Query from "../Model/Search/Query";
import ResultSet from "../Model/Search/ResultSet";
import { AuthService } from "../shared/auth.service";
import { Subject } from "rxjs/Subject";
import {Text} from "./text.service";

const MIN_HOT_SCORE = 18;

@Injectable()
export class SearchService {

    resultSet: ResultSet;
    hotResultSet: ResultSet;
    private queryObservable = new Subject<Query>();
    private queryHotObservable = new Subject<Query>();

    constructor(protected text: Text, private http: Http, private auth: AuthService) {

        this.resultSet = new ResultSet(text.get('productions', 'productions'));
        this.hotResultSet = new ResultSet(text.get('hot', 'Point dur'));

        this.queryObservable
            .switchMap((query: Query) => this.doRequest(query))
            .subscribe((res) => this.resultSet.update(res))
        ;

        this.hotResultSet.setMinScoreToShow(MIN_HOT_SCORE);

        this.queryHotObservable
            .switchMap((query: Query) => this.doHotRequest(query))
            .subscribe((res) => this.hotResultSet.update(res))
        ;
    }

    getResultSet(): ResultSet {
        return this.resultSet;
    }

    getHotResultSet(): ResultSet {
        return this.hotResultSet;
    }

    searchHot(query: Query): void {
        this.queryHotObservable.next(query);
    }

    search(query: Query): void {
        this.queryObservable.next(query);
    }

    private doRequest(query: Query): Observable<ResultSet> {
        return this.http
            .get(process.env.ENGINE_HOST, this.getQueryParams(query))
            .map(res => res.json())
        ;
    }

    private doHotRequest(query: Query): Observable<ResultSet> {
        return this.http
            .get(process.env.ENGINE_HOST+'/hot', this.getQueryParams(query))
            .map(res => res.json())
        ;
    }

    private getQueryParams(query: Query): RequestOptions {
        let params: URLSearchParams = new URLSearchParams();

        params.set('index', query.index);
        params.set('term', query.term);
        params.set('size', query.getSize().toString());
        params.set('from', query.from.toString());
        params.set('findByPopin', query.findByPopin ? '1' : '0');

        for (let filterKey in query.getFilters()) {
            let filter = query.getFilters()[filterKey];

            params.set(`filters[${filterKey}][field]`, filter.field);
            params.set(`filters[${filterKey}][value]`, filter.value);
        }

        params.set('token', this.auth.getToken());

        let requestOptions = new RequestOptions();
        requestOptions.params = params;

        return requestOptions;
    }
}
