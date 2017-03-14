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

        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournament/?game=' + this.currentGame.abbriviature + '&id=1')
        .then(function (response) {
            self.game = response.data;

            self.loading = false;
        });
    },
    methods: {
        getGameData: function(gameName) {
            const abbr = '';
            const name = '';

            switch(gameName) {
                case 'league-of-legends':
                    abbr = 'lol';
                    name = 'League of Legends';
                break;
                case 'hearthstone':
                    abbr = 'hs';
                    name = 'Hearthstone';
                break;
                default:
                    abbr = 'lol';
                    name = 'League of Legends';
                break;
            }

            return { name: name, abbriviature: abbr };
        }
    }
};