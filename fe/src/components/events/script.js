const LolTournaments = {
    template: '#events-template',
    data: function() {
        return {
            loading: true,
            games: {}
        };
    },
    created: function() {
        var self = this;

        let game = this.getGameAbbriviature($route.params.game);

        axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournaments/?game=' + game + '&limit=20')
        .then(function (response) {
            self.games = response.data;

            self.loading = false;
        });
    },
    methods: {
        getGameAbbriviature: function(gameName) {
            switch(gameName) {
                case 'league-of-legends':
                    return 'lol';
                break;
            }
        }
    }
};