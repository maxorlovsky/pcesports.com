const Events = {
    template: '#events-template',
    data: function() {
        return {
            loading: true,
            games: {},
            currentGame: '',
        };
    },
    created: function() {
        var self = this;

        this.currentGame = this.getGameData(this.$route.params.game);

        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournaments/?game=' + this.currentGame.abbriviature + '&limit=20')
        .then(function (response) {
            self.games = response.data;

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