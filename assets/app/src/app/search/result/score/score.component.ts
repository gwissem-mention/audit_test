import {Component, Input, OnInit} from '@angular/core';

@Component({
    selector: 'score',
    styleUrls: ['./score.component.scss'],
    templateUrl: './score.component.html',
})
export class ScoreComponent implements OnInit {
    @Input()
    score: number;

    max: number = 3;
    bars: Array<Object> = [];

    constructor() {
    }

    ngOnInit() {
        for (let i = 1; i <= this.max ; i++) {
            this.bars.push({
                enabled: i <= this.score,
                percent: i * 100 / this.max
            });
        }
    }
}
