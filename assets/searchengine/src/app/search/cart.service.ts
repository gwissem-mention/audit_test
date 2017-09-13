import { Injectable } from '@angular/core';
import {Http, RequestOptionsArgs, Headers} from '@angular/http';
import Result from "../Model/Search/Result";

declare let Routing: any;

@Injectable()
export default class Cart {

    cartMessage: (cssClass: string, message: string) => void;

    constructor(private http: Http) {
        this.cartMessage = function (cssClass: string, message: string): void {
            $.fancybox({
                content: $('<div class="alert alert-block nalert-'+cssClass+'">' + message + '</div>'),
                autoSize: false,
                autoHeight: true,
                width: 700
            });
        }
    }

    addToCart(result: Result): void {
        let addToCarturl = Routing.generate('hopital_numerique_cart_add', {
            'objectType': result.getType(),
            'objectId': result.getId()
        });
        
        let headers: Headers = new Headers();
        headers.append('X-Requested-With', 'XMLHttpRequest');
        let options:RequestOptionsArgs = { headers: headers };

        this.http.get(addToCarturl, options).subscribe(data => {
            this.cartMessage('success', data.json().message);
        }, error => {
            this.cartMessage('danger', JSON.parse(error._body).message);
        });
    }
}