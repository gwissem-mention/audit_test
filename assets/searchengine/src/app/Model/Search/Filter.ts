import Aggregation from "./Aggregation";
export default class Filter {
    label: string;
    value: string;
    field: string;

    public static createFromAggregation(aggregation: Aggregation): Filter {
        let filter = new Filter;
        filter.field = aggregation.field;
        filter.value = aggregation.value;
        filter.label = aggregation.label;

        return filter;
    }
}
