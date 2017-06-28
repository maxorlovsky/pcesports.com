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
            loadingMore: false,
            seoText: ''
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
                this.currentGame = pce.getGameData(this.$route.params.game);
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

            axios.get('https://api.pcesports.com/tournaments' + filter)
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

                if (gamesFiltered.length < self.limit || response.data.message === 'no-events') {
                    self.hideLoadMore = true;
                }
                else {
                    self.hideLoadMore = false;
                }

                self.loading = false;
                self.loadingMore = false;
            });

            this.seoText = this.loadSeo(this.$route.params.game);
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
        },
        loadSeo: function(game) {
            let text = '';

            switch(game) {
                case 'smite':
                    text = '<h1>Smite tournaments</h1>\
                    <h2>List of Competitive Smite tournaments</h2>\
                    <p>Find all competitive Smite tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'dota':
                    text = '<h1>Dota 2 tournaments</h1>\
                    <h2>List of Competitive Dota 2 tournaments</h2>\
                    <p>Find all competitive Dota tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'counter-strike':
                    text = '<h1>Counter Strike tournaments</h1>\
                    <h2>List of Competitive Counter Strike tournaments</h2>\
                    <p>Find all competitive Counter Strike "CSGO" tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'rocket-league':
                    text = '<h1>Rocket League tournaments</h1>\
                    <h2>List of Competitive Rocket League tournaments</h2>\
                    <p>Find all competitive Rocket League tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'heroes-of-the-storm':
                    text = '<h1>Heroes of the Storm tournaments</h1>\
                    <h2>List of Competitive Heroes of the Storm tournaments</h2>\
                    <p>Find all competitive Heroes of the Storm tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'league-of-legends':
                    text = '<h1>League of Legends tournaments</h1>\
                    <h2>List of Competitive League of Legends tournaments</h2>\
                    <p>Find all competitive League of Legends tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'hearthstone':
                    text = '<h1>Hearthstone tournaments</h1>\
                    <h2>List of Competitive Hearthstone tournaments</h2>\
                    <p>Find all competitive Hearthstone tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'overwatch':
                   text = '<h1>Overwatch tournaments</h1>\
                    <h2>List of Competitive Overwatch tournaments</h2>\
                    <p>Find all competitive Overwatch tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                default:
                    text = '<h1>List of Competitive gaming tournaments</h1>\
                    <p>Find all competitive gaming tournaments around North America and Europe.</p>\
                    <p>List include games like League of Legends, Hearthstone, Overwatch, Rocket League, Heroes of the Storm, Dota 2, Counter-Strike: Global Offensive, Smite, full list of what gamer might need</p>\
                    <p>Get your team to find the perfect competitive esports tournament for you all around North America or Europe region</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
            }

            return text;
        }
    }
};