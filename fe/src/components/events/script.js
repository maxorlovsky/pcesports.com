const Events = {
    template: '#events-template',
    data: function() {
        return {
            loading: true,
            games: {},
            currentGame: {},
        };
    },
    created: function() {
        return this.fetchEventData();
    },
    watch: {
        $route: 'fetchEventData'
    },
    methods: {
        fetchEventData: function() {
            this.loading = true;

            let self = this;
            let filter = '?';

            if (this.$route.params.game) {
                this.currentGame = this.getGameData(this.$route.params.game);
                filter += '&game=' + this.currentGame.abbriviature;
            } else {
                this.currentGame.name = 'All';
            }

            filter += '&limit=25';

            axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournaments/' + filter)
            .then(function (response) {
                self.games = response.data;

                self.loading = false;
            });
        },
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
        },
        getGameLink: function(gameAbbriviature) {
            let link = '';

            switch(gameAbbriviature) {
                case 'lol':
                    link = 'league-of-legends';
                break;
                case 'hs':
                    link = 'hearthstone';
                break;
            }

            return link;
        }
    }
};