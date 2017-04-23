const Events = {
    template: '#events-template',
    data: function() {
        return {
            loading: true,
            games: {},
            currentGame: {},
            status: {
                name: 'Status',
                list: ['All', 'Started', 'Starting soon', 'Upcoming'],
                current: 'All'
            },
            region: {
                name: 'Region',
                list: {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                },
                current: 'all'
            },
            searchString: '',
            searchStatus: false,
            hideLoadMore: true,
            limit: 40,
            offset: 0,
            loadingMore: false
        };
    },
    created: function() {
        return this.fetchEventData();
    },
    watch: {
        $route: function() {
            this.searchString = '';
            this.offset = 0;
            this.fetchEventData();
        }
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

            filter += '&limit=' + this.limit;

            if (this.region.current && this.region.current != 'All') {
                filter += '&region='+this.region.current.toLowerCase();
            }

            if (this.status.current && this.status.current != 'All') {
                filter += '&status='+this.status.current.toLowerCase();
            }

            if (this.searchString && this.searchString.length >= 3) {
                filter += '&search='+this.searchString;
            }

            if (this.offset) {
                filter += '&offset=' + this.offset;
            }

            axios.get('https://api.pcesports.com/wp/wp-json/pce-api/tournaments/' + filter)
            .then(function (response) {
                let gamesFiltered = response.data;

                let currentDate = new Date();
                let timezoneOffset = currentDate.getTimezoneOffset() * 60;

                for (let i = 0; i < gamesFiltered.length; i++) {
                    let date = new Date((gamesFiltered[i].startTime - timezoneOffset) * 1000);

                    gamesFiltered[i].name = gamesFiltered[i].name
                        .replace(/&amp;/g, "&")
                        .replace(/&gt;/g, ">")
                        .replace(/&lt;/g, "<")
                        .replace(/&quot;"/g, "\"");
                    date = date.toUTCString();
                    gamesFiltered[i].startTime = date.substring(0, (date.length - 7));
                }

                if (self.offset) {
                    let combinedArray = self.games.concat(gamesFiltered);
                    self.games = combinedArray;
                }
                else {
                    self.games = gamesFiltered;
                }

                if (gamesFiltered.length < self.limit) {
                    self.hideLoadMore = true;
                }
                else {
                    self.hideLoadMore = false;
                }

                self.loading = false;
                self.loadingMore = false;
            });
        },
        getGameData: function(gameName) {
            const game = {};

            switch(gameName) {
                case 'smite':
                    game.abbriviature = 'smite';
                    game.name = 'Smite';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'eu': 'Europe'
                    };
                break;
                case 'dota':
                    game.abbriviature = 'dota';
                    game.name = 'Dota 2';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'euw': 'Europe West',
                        'eune': 'Europe East'
                    };
                break;
                case 'counter-strike':
                    game.abbriviature = 'cs';
                    game.name = 'Counter Strike:GO';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'eu': 'Europe'
                    };
                break;
                case 'rocket-league':
                    game.abbriviature = 'rl';
                    game.name = 'Rocket League';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'eu': 'Europe'
                    };
                break;
                case 'heroes-of-the-storm':
                    game.abbriviature = 'hots';
                    game.name = 'Heroes of the Storm';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'eu': 'Europe'
                    };
                break;
                case 'overwatch':
                    game.abbriviature = 'ow';
                    game.name = 'Overwatch';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'eu': 'Europe'
                    };
                break;
                case 'hearthstone':
                    game.abbriviature = 'hs';
                    game.name = 'Hearthstone';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'eu': 'Europe'
                    };
                break;
                default:
                    game.abbriviature = 'lol';
                    game.name = 'League of Legends';
                    game.regions = {
                        'all': 'All',
                        'na': 'North America',
                        'euw': 'Europe West',
                        'eune': 'Europe East'
                    };
                break;
            }

            return game;
        },
        cleanSearch: function(keyAction) {
            if (keyAction && !this.searchString) {
                this.searchStatus = false;
                return false;
            }
            else if (keyAction) {
                this.searchStatus = true;
                return false;
            }
            
            if (this.searchString.length === 0 && this.searchStatus) {
                // Need to delay the function call
                setTimeout(() => {
                    this.fetchEventData();
                });
            }
        },
        loadMore: function() {
            this.loadingMore = true;
            this.offset += this.limit;

            this.fetchEventData();
        }
    }
};