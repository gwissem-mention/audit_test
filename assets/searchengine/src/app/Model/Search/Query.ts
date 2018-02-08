import Filter from "./Filter";

export default class Query {
    term: string = '';
    findByPopin: boolean;

    offset: number = 0;
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
        this.offset = 0;
    }

    setFindByPopin(findBypopin: boolean) {
        this.findByPopin = findBypopin;
    }

    /**
     * Show more results
     */
    more() {
        this.currentPage++;
    }

    /**
     * Show less results
     */
    less() {
        this.currentPage--;
    }

    getSize(): number {
        return this.offset + this.currentPage * this.itemsPerPage;
    }

    getItemPerPage(): number {
        return this.itemsPerPage;
    }

    setItemsPerPage(size: number) {
        this.itemsPerPage = size;
    }

    getCurrentPage(): number {
        return this.currentPage;
    }

    setCurrentPage(page: number) {
        this.currentPage = page;
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

    isEmpty(): boolean {
        return this.term == ''
            && this.filters.length === 0
            ;
    }
}
