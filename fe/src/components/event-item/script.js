Vue.component('event-item', {
    template: dynamicTemplates.eventItem,
    props: {
        'game': {
            type: Object,
            required: true
        }
    },
    data: function() {
        return {
            game: this.game
        };
    },
    created: function() {
        
    },
    methods: {
        getGameLink: function(gameAbbriviature) {
            let link = '';

            switch(gameAbbriviature) {
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