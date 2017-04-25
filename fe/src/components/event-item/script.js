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
            let game = pce.getGameData(gameAbbriviature);
            
            return game.link;
        },
    }
});