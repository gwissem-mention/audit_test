import { NgModule, APP_INITIALIZER } from '@angular/core';
import { BrowserModule }  from '@angular/platform-browser';
import { SearchModule }  from './search/search.module';
import { SearchComponent }  from './search/search.component';
import {Config} from "./app.config";
import {AuthService} from "./shared/auth.service";

@NgModule({
    imports: [
        BrowserModule,
        SearchModule
    ],
    providers: [
        Config,
        AuthService,
        {
            provide: APP_INITIALIZER,
            useFactory: (config: Config) => () => config.load(),
            deps: [Config],
            multi: true
        }
    ],
    bootstrap: [ SearchComponent ]
})
export class AppModule {}
