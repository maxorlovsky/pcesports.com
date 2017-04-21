const EventDetails = {
    template: '#event-details-template',
    data: function() {
        return {
            loading: true,
            pageError: '',
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

            document.title += ' - '+ self.game.name;

            self.game.meta_data = JSON.parse(self.game.meta_data);

            self.game.meta_data.prizes = self.game.meta_data.prizes.replace(/(?:\r\n|\r|\n)/g, '<br />');
            if (self.game.platform === 'battlefy' && (self.game.game === 'ow' || self.game.game === 'hots')) {
                self.game.meta_data.description = self.correctDescription(self.game.meta_data.description, false);
            } else {
                self.game.meta_data.description = self.correctDescription(self.game.meta_data.description, true);
            }
            self.game.platform = self.correctPlatform(self.game.platform);
            self.game.start_datetime = self.correctDate(self.game.start_datetime);
            self.game.meta_data.elimination = self.correctEventFormat(self.game.meta_data.elimination);
            if (self.game.meta_data.free) {
                self.game.meta_data.free = self.correctPayment(self.game.meta_data.free);
            }
            if (self.game.meta_data.online) {
                self.game.meta_data.online = self.correctOnline(self.game.meta_data.online);
            }
            
            self.loading = false;
        })
        .catch(function (error) {
            self.pageError = error.response.data.message;
            self.loading = false;
        });
    },
    methods: {
        getGameData: function(gameName) {
            const game = {};

            switch(gameName) {
                case 'smite':
                    game.abbriviature = 'smite';
                    game.name = 'Smite';
                break;
                case 'dota':
                    game.abbriviature = 'dota';
                    game.name = 'Dota 2';
                break;
                case 'counter-strike':
                    game.abbriviature = 'cs';
                    game.name = 'Counter Strike:GO';
                break;
                case 'rocket-league':
                    game.abbriviature = 'rl';
                    game.name = 'Rocket League';
                break;
                case 'heroes-of-the-storm':
                    game.abbriviature = 'hots';
                    game.name = 'Heroes of the Storm';
                break;
                case 'league-of-legends':
                    game.abbriviature = 'lol';
                    game.name = 'League of Legends';
                break;
                case 'hearthstone':
                    game.abbriviature = 'hs';
                    game.name = 'Hearthstone';
                break;
                case 'overwatch':
                    game.abbriviature = 'ow';
                    game.name = 'Overwatch';
                break;
                default:
                    game.abbriviature = 'lol';
                    game.name = 'League of Legends';
                break;
            }

            return game;
        },
        correctDate: function(timeStamp) {
            const currentDate = new Date();
            const timezoneOffset = currentDate.getTimezoneOffset() * 60;

            let date = new Date((timeStamp - timezoneOffset) * 1000);
            date = date.toUTCString();
            date = date.substring(0, (date.length - 7));

            return date;
        },
        correctDescription: function(description, markedEnable) {
            if (markedEnable) {
                description = marked(description);
            }

            description = description
                .replace(/&amp;/g, "&")
                .replace(/&gt;/g, ">")
                .replace(/&lt;/g, "<")
                .replace(/&quot;"/g, "\"");

            return description;
        },
        correctEventFormat: function(format) {
            let fullName = '';

            switch(format) {
                case 'single-elim':
                case 'Standard':
                    fullName = 'Single Elimination';
                break;
                case 'double-elim':
                    fullName = 'Double Elimination';
                break;
                case 'Last Hero Standing':
                    fullName = 'Last Hero Standing';
                break;
                default:
                    fullName = 'Not specified';
                break;
            }

            return fullName;
        },
        correctPayment: function(free) {
            let status = '';

            if (free && free === true) {
                status = 'Free';
            }
            else if (free) {
                status = 'Payment required';
            }

            return status;
        },
        correctOnline: function(online) {
            let status = '';

            if (online && online === true) {
                status = 'Yes';
            }
            else if (online) {
                status = 'No';
            }

            return status;
        },
        correctPlatform: function(platform) {
            let fullName = {
                name: 'Custom',
                image: false
            };

            if (platform === 'battlefy') {
                fullName.name = 'Battlefy';
                fullName.image = 'battlefy';
            }
            else if (platform === 'strive-wire') {
                fullName.name = 'StriveWire';
                fullName.image = 'strive-wire';
            }
            else if (platform === 'toornament') {
                fullName.name = 'Toornament';
                fullName.image = 'toornament';
            }
            else if (platform === 'esportswall') {
                fullName.name = 'eSports Wall';
                fullName.image = 'esportswall';
            }
            else if (platform === 'go4') {
                fullName.name = 'Go4 ESL';
                fullName.image = 'esl';
            }

            return fullName;
        }
    }
};