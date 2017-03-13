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

        this.currentGame = this.getGameAbbriviature(this.$route.params.game);

        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournaments/?game=' + this.currentGame + '&limit=20')
        .then(function (response) {
            self.games = response.data;

            self.loading = false;
        });
    },
    methods: {
        getGameAbbriviature: function(gameName) {
            let game = '';

            switch(gameName) {
                case 'league-of-legends':
                    game = 'lol';
                break;
                case 'hearthstone':
                    game = 'hs';
                break;
                default:
                    game = 'lol';
                break;
            }

            return game;
        }
    }
};