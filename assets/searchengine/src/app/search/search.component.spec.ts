import { TestBed } from '@angular/core/testing';
import { SearchComponent } from './search.component';
describe('Search', () => {
    beforeEach(() => {
        TestBed.configureTestingModule({ declarations: [SearchComponent]});
    });
    it ('should work', () => {
        let fixture = TestBed.createComponent(SearchComponent);
        expect(fixture.componentInstance instanceof SearchComponent).toBe(true, 'should create SearchComponent');
    });
});
