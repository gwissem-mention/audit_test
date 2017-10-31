import { Injectable } from '@angular/core';
import {Http, RequestOptionsArgs, Headers} from '@angular/http';
import Result from "../Model/Search/Result";
import {Config} from "../app.config";
import Publication from "../Model/Search/Result/Publication";
import ForumTopic from "../Model/Search/Result/ForumTopic";
import Person from "../Model/Search/Result/Person";
import Group from "../Model/Search/Result/Group";

declare let $ : any;
declare let Routing: any;

@Injectable()
export default class Cart {

    cartMessage: (cssClass: string, message: string) => void;

    constructor(private http: Http, private config: Config) {
        this.cartMessage = function (cssClass: string, message: string): void {
            $.fancybox({
                content: $('<div class="alert alert-block nalert-'+cssClass+'">' + message + '</div>'),
                autoSize: false,
                autoHeight: true,
                width: 700
            });
        }
    }

    getCartItemType(result: any): string {
        switch (true) {
            case result instanceof Publication:
                return result.hasParent() ? 'contenu' : 'objet';
            case result instanceof ForumTopic:
                return 'forum_topic';
            case result instanceof Person:
                return 'person';
            case result instanceof Group:
                return 'cdp_group';
        }
        return null;
    }

    canShow(result: Result): boolean {
        return null !== this.getCartItemType(result) && this.config.get('options').showCart;
    }

    addToCart(result: Result): void {
        let addToCartUrl = Routing.generate('hopital_numerique_cart_add', {
            'objectType': this.getCartItemType(result),
            'objectId': result.getId()
        });
        
        let headers: Headers = new Headers();
        headers.append('X-Requested-With', 'XMLHttpRequest');
        let options:RequestOptionsArgs = { headers: headers };

        this.http.get(addToCartUrl, options).subscribe(data => {
            this.cartMessage('success', data.json().message);
        }, error => {
            this.cartMessage('danger', JSON.parse(error._body).message);
        });
    }
}
