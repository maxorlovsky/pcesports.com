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

            filter += '&limit=40';

            axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournaments/' + filter)
            .then(function (response) {
                self.games = response.data;

                let currentDate = new Date();
                let timezoneOffset = currentDate.getTimezoneOffset() * 60;

                for (let i = 0; i < self.games.length; i++) {
                    let date = new Date((self.games[i].startTime - timezoneOffset) * 1000);

                    self.games[i].name = self.games[i].name
                        .replace(/&amp;/g, "&")
                        .replace(/&gt;/g, ">")
                        .replace(/&lt;/g, "<")
                        .replace(/&quot;"/g, "\"");
                    self.games[i].startTime = date.toUTCString().replace(':00 GMT', '');
                }

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