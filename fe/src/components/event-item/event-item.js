Vue.component('event-item', {
    template: dynamicTemplates.eventItem,
    props: {
        'game': {
            type: Object,
            required: true
        },
        'editable': {
            type: Boolean,
            default: false
        }
    },
    data: function() {
        if (this.game.participantsLimit === '0') {
            this.game.participantsLimit = 'Unlimited';
        }

        return {
            game: this.game,
            link: this.editable ? '/event/add' : '/events'
        };
    },
    methods: {
        getGameLink: function(gameAbbriviature) {
            let game = pce.getGameData(gameAbbriviature);
            
            return game.link;
        },
    }
});