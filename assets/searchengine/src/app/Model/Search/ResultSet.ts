import Result from "./Result";
import {Subject} from "rxjs/Subject";
import Aggregation from "./Aggregation";
import ResultFactory from "./ResultFactory";

export default class ResultSet {
    total: number;

    maxScore: number;

    exactMatches: number;

    minScoreToShow: number = null;

    results = new Subject<Result[]>();

    aggregations = new Subject<Aggregation[]>();

    constructor (public label: string) {
    }

    setTotal(total: number) {
        this.total = total;
    }

    setMinScoreToShow(score: number) {
        this.minScoreToShow = score;
    }

    getMinScoreToShow(): number {
        return this.minScoreToShow;
    }

    update(rawResult: any) {
        this.setTotal(rawResult.hits.total);
        this.maxScore = rawResult.hits.max_score;
        this.results.next(this.extractResults(rawResult));
        this.aggregations.next(this.extractAggregations(rawResult.aggregations));
    }

    private extractResults(body: any) {
        let results = [];

        for (let resultData of body.hits.hits) {
            let result = ResultFactory.getResult(resultData);
            if (result !== undefined) {
                results.push(result);
            }
        }

        return results;
    }

    private extractAggregations(body: any): Array<Aggregation> {
        let aggregations: Aggregation[] = [];

        for (let aggData of body.types.buckets) {
            let hasSubAggregation:boolean = false;
            for (let property of Object.getOwnPropertyNames(aggData)) {
                if (-1 === ['doc_count', 'key'].indexOf(property)) {
                    for (let subAggData of aggData[property].types.buckets) {
                        this.addAggregation(aggregations, this.parseAggregation(subAggData, 'types.libelle'));
                        hasSubAggregation = true;
                    }
                }
            }

            if (!hasSubAggregation) {
                this.addAggregation(aggregations, this.parseAggregation(aggData, '_type'));
            }
        }

        aggregations = aggregations.sort((agg1, agg2) => {
            if (agg1.count > agg2.count) {
                return -1;
            }

            if (agg1.count < agg2.count) {
                return 1;
            }

            return 0;
        });

        if (undefined !== body.exact_results) {
            this.exactMatches = body.exact_results.buckets[0].doc_count;
        }

        return aggregations;
    }

    private parseAggregation(aggData: any, field: string = 'types'): Aggregation {
        let aggregation = new Aggregation;
        aggregation.label = aggData.key;
        aggregation.value = aggData.key;
        aggregation.count = aggData.doc_count;
        aggregation.field = field;

        return aggregation;
    }

    private addAggregation(aggregations: Aggregation[], aggregation: Aggregation): void {
        let existentAggregation = aggregations.find(agg => agg.label === aggregation.label);
        let index = aggregations.indexOf(existentAggregation, 0);

        if (index !== -1) {
            aggregations[index].count += aggregation.count;
        } else {
            aggregations.push(aggregation);
        }
    }
}
