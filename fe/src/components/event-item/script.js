Vue.component('event-item', {
    template: dynamicTemplates.eventItem,
    props: {
        'game': {
            type: Object,
            required: true
        }
    },
    data: function() {
        if (this.game.participantsLimit === '0') {
            this.game.participantsLimit = 'Unlimited';
        }

        return {
            game: this.game
        };
    },
    methods: {
        getGameLink: function(gameAbbriviature) {
            let link = '';

            switch(gameAbbriviature) {
                case 'smite':
                    link = 'smite';
                break;
                case 'dota':
                    link = 'dota';
                break;
                case 'cs':
                    link = 'counter-strike';
                break;
                case 'rl':
                    link = 'rocket-league';
                break;
                case 'hots':
                    link = 'heroes-of-the-storm';
                break;
                case 'lol':
                    link = 'league-of-legends';
                break;
                case 'hs':
                    link = 'hearthstone';
                break;
                case 'ow':
                    link = 'overwatch';
                break;
            }

            return link;
        },
    }
});