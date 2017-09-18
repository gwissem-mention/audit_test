import { NgModule } from '@angular/core';
import { HttpModule }    from '@angular/http';
import { SearchComponent } from './search.component';
import { QueryComponent } from './query/query.component';
import {BrowserModule} from "@angular/platform-browser";
import {ResultComponent} from "./result/result.component";
import {ScoreComponent} from "./result/score/score.component";
import {AggregationComponent} from "./result/aggregation/aggregation.component";
import {Text} from "./text.service";
import Cart from "./cart.service";

@NgModule({
    imports: [
        HttpModule,
        BrowserModule,
    ],
    providers: [
        Text,
        Cart,
    ],
    declarations: [
        SearchComponent,
        QueryComponent,
        ResultComponent,
        ScoreComponent,
        AggregationComponent
    ],
    bootstrap: [ SearchComponent ]
})
export class SearchModule { }
