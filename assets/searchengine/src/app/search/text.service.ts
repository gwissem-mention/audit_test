import { Injectable } from '@angular/core';
import {Config} from "../app.config";

@Injectable()
/**
 * Text service allow to get text mapping from configuration `texts` key
 */
export class Text {

    constructor(protected config: Config) {}

    /**
     * Get text mapping for $key, or $placeholder if not defined
     *
     * @param key
     * @param placeholder
     *
     * @returns string
     */
    public get(key: string, placeholder: any = ''): string {
        let texts = this.config.get('texts');
        let path = key.split('.');

        while (path.length > 0) {
            let part = path.shift();

            if (!texts.hasOwnProperty(part)) {
                return placeholder;
            }
            texts = texts[part];
        }

        return texts;
    }
}
