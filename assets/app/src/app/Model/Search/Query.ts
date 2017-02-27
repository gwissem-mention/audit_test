import Filter from "./Filter";

export default class Query {
    term: string = '';

    size: number = 10;
    from: number = 0;

    index: string;

    private currentPage: number = 1;
    private itemsPerPage: number = 10;

    protected filters: Filter[] = [];

    constructor(index: string) {
        this.index = index;
    }

    setTerm(term: string) {
        this.term = term.trim();
    }

    /**
     * Show more results
     */
    more() {
        this.size += this.getItemPerPage();
    }

    /**
     * Show less results
     */
    less() {
        this.size -= this.getItemPerPage();
    }

    getItemPerPage(): number {
        return this.itemsPerPage;
    }

    setItemsPerPage(items: number) {
        this.itemsPerPage = items;
        // @TODO
    }

    getFilters(): Filter[] {
        return this.filters;
    }

    addFilter(filter: Filter) {

        if (this.filterExists(filter)) {
            this.filters.push(filter);
        }
    }

    filterExists(filter: Filter): boolean {
        return this.filters.find(f => f.field === filter.field && f.value === filter.value && f.label === filter.label) === undefined;
    }

    removeFilter(filter: Filter) {
        let index = this.filters.indexOf(filter, 0);
        if (index !== -1) {
            this.filters.splice(index, 1);
        }
    }
}
