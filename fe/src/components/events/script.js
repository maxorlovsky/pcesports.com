const Events = {
    template: '#events-template',
    data: function() {
        return {
            loading: true,
            games: {},
            currentGame: {},
            status: {
                name: 'Status',
                list: ['Started', 'Starting soon', 'Upcoming'],
                open: false,
                current: 'All'
            },
            region: {
                name: 'Region',
                list: {},
                open: false,
                current: 'All'
            },
            searchString: '',
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
                this.region.list = this.currentGame.regions;
            } else {
                this.currentGame.name = 'All';
            }

            filter += '&limit=40';

            if (this.region.current && this.region.current != 'All') {
                filter += '&region='+this.region.current.toLowerCase();
            }

            if (this.status.current && this.status.current != 'All') {
                filter += '&status='+this.status.current.toLowerCase();
            }

            if (this.searchString && this.searchString.length >= 3) {
                filter += '&search='+this.searchString;
            }

            axios.get('http://dev.api.pcesports.com/wp/wp-json/pce-api/tournaments/' + filter)
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
                case 'hearthstone':
                    game.abbriviature = 'hs';
                    game.name = 'Hearthstone';
                    game.regions = {
                        'na': 'North America',
                        'eu': 'Europe',
                    };
                break;
                default:
                    game.abbriviature = 'lol';
                    game.name = 'League of Legends';
                    game.regions = {
                        'na': 'North America',
                        'euw': 'Europe West',
                        'eune': 'Europe East'
                    };
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
        },
        closeDropdown: function(event) {
            if (event.toElement.className.indexOf('dropdown') !== - 1) {
                return false;
            }

            this.status.open = false;
            this.region.open = false;
        }
    }
};