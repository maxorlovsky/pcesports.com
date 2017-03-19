const EventDetails = {
    template: '#event-details-template',
    data: function() {
        return {
            loading: true,
            game: {},
        };
    },
    created: function() {
        var self = this;

        this.currentGame = this.getGameData(this.$route.params.game);
        this.eventId = parseInt(this.$route.params.event);

        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournament/?game=' + this.currentGame.abbriviature + '&id='+this.eventId)
        .then(function (response) {
            self.game = response.data;

            self.game.name = self.game.name
                        .replace(/&amp;/g, "&")
                        .replace(/&gt;/g, ">")
                        .replace(/&lt;/g, "<")
                        .replace(/&quot;"/g, "\"");

            self.game.meta_data = JSON.parse(self.game.meta_data);

            self.loading = false;
        });
    },
    methods: {
        getGameData: function(gameName) {
            const game = {};

            switch(gameName) {
                case 'league-of-legends':
                    game.abbriviature = 'lol';
                    game.name = 'League of Legends';
                break;
                case 'hearthstone':
                    game.abbriviature = 'hs';
                    game.name = 'Hearthstone';
                break;
                default:
                    game.abbriviature = 'lol';
                    game.name = 'League of Legends';
                break;
            }

            return game;
        }
    }
};