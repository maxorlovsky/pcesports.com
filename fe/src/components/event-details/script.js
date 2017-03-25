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

            self.game.meta_data = JSON.parse(self.game.meta_data);

            self.game.meta_data.prizes = self.game.meta_data.prizes.replace(/(?:\r\n|\r|\n)/g, '<br />');
            self.game.meta_data.description = self.correctDescription(self.game.meta_data.description);
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
        correctDate: function(timeStamp) {
            const currentDate = new Date();
            const timezoneOffset = currentDate.getTimezoneOffset() * 60;

            let date = new Date((timeStamp - timezoneOffset) * 1000);
            date = date.toUTCString().replace(':00 GMT', '');

            return date;
        },
        correctDescription: function(description) {
            description = marked(description)
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
                    fullName = 'Single Elimination';
                break;
                case 'double-elim':
                    fullName = 'Double Elimination';
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
            else if (platform === 'esports-wall') {
                fullName.name = 'eSports Wall';
                fullName.image = 'esports-wall';
            }

            return fullName;
        }
    }
};