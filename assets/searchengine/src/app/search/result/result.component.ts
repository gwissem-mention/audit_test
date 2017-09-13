import {Component, Input, Output, OnInit, EventEmitter, ViewEncapsulation} from '@angular/core';
import ResultSet from "../../Model/Search/ResultSet";
import Query from "../../Model/Search/Query";
import Result from "../../Model/Search/Result";
import {DomSanitizer, SafeUrl} from '@angular/platform-browser';
import {Text} from "../text.service";
import Cart from "../cart.service";
import {Config} from "../../app.config";

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
    allHidden = false;
    results: Array<Result>;

    @Output()
    queryChanged: EventEmitter<Query> = new EventEmitter<Query>();

    constructor(private sanitizer:DomSanitizer, protected text: Text, protected cartService: Cart) {

    }

    showMore() {
        this.query.more();
        this.queryChanged.emit(this.query);
    }

    showLess() {
        this.query.less();
        this.queryChanged.emit(this.query);
    }

    sanitizeUrl(url: string): SafeUrl {
        return this.sanitizer.bypassSecurityTrustUrl(url);
    }

    ngOnInit() {
        this.resultSet.results.subscribe((results) => {
            let minScore = this.resultSet.getMinScoreToShow();

            if (null !== minScore && this.query.getCurrentPage() === 1) {
                this.results = [];
                for (let result of results) {
                    if (result.getRawScore() > minScore) {
                        this.results.push(result);
                    } else {
                        this.query.offset--;
                    }
                }
            } else {
                this.results = results;
            }

            let resultLength = this.results.length;

            this.allHidden = resultLength === 0 && this.resultSet.total > 0;
            this.canShowMore = resultLength < this.resultSet.total;
            this.canShowLess = this.query.getCurrentPage() > 1;
            this.canShow = this.resultSet.total > 0;
        });
    }

    canShowCart(): boolean {
        return this.cartService.canShow();
    }

    addToCart(result: Result) {
        this.cartService.addToCart(result);
    }
}
