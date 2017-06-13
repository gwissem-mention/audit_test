import { Injectable } from '@angular/core';

@Injectable()
export class Config {
    protected config: Object = {};

    public load() {
        return new Promise((resolve, reject) => {
            let storage = sessionStorage.getItem('searchengine');
            this.config = storage ? JSON.parse(storage) : {};

            resolve(true);
        });
    }

    public get(key: string): any {
        if (this.config.hasOwnProperty(key)) {
            return this.config[key];
        }
        return null;
    }
}
