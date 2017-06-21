import {Component, Input, Output, EventEmitter} from '@angular/core';
import ResultSet from "../../../Model/Search/ResultSet";
import Query from "../../../Model/Search/Query";
import Aggregation from "../../../Model/Search/Aggregation";
import Filter from "../../../Model/Search/Filter";
import {Text} from "../../text.service";

@Component({
    selector: 'search-aggregation',
    // styleUrls: ['./result.component.scss'],
    templateUrl: './aggregation.component.html',
})
export class AggregationComponent {
    @Input()
    resultSet: ResultSet;

    @Input()
    query: Query;

    @Output()
    queryChanged: EventEmitter<Query> = new EventEmitter<Query>();

    constructor(protected text: Text) {}

    addFilter(aggregation: Aggregation): void {
        this.query.addFilter(Filter.createFromAggregation(aggregation));
        this.queryChanged.emit(this.query);
    }

    isAggregationDisplayable(aggregation: Aggregation): boolean {
        return this.query.filterExists(Filter.createFromAggregation(aggregation));
    }
}
