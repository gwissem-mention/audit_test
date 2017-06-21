import { Injectable } from '@angular/core';

@Injectable()
export class AuthService {

    private token: string;

    constructor() {
        this.token = sessionStorage.getItem('authentication_token');
    }

    getToken():string {
        return this.token;
    }
}
