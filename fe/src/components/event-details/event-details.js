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
        const self = this;

        this.currentGame = pce.getGameData(this.$route.params.game);
        this.eventId = parseInt(this.$route.params.event);

        axios.get('https://api.pcesports.com/tournament/' + this.currentGame.abbriviature + '/' + this.eventId)
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
            } else if (self.game.platform === 'esl') {
                self.game.meta_data.description = self.correctDescription(self.game.meta_data.description, false);
            } else {
                self.game.meta_data.description = self.correctDescription(self.game.meta_data.description, true);
            }
            self.game.platform = {
                name: self.game.platformName,
                image: self.game.platformName === 'Custom' ? false : self.game.platform
            };
            self.game.start_datetime = self.correctDate(self.game.start_datetime);
            self.game.meta_data.elimination = self.correctEventFormat(self.game.meta_data.elimination);
            if (self.game.meta_data.free) {
                self.game.meta_data.free = self.correctPayment(self.game.meta_data.free);
            }
            if (self.game.meta_data.online) {
                self.game.meta_data.online = self.correctOnline(self.game.meta_data.online);
            }

            if (self.game.participants_limit === '0') {
                self.game.participants_limit = 'Unlimited';
            }
            
            self.loading = false;
        })
        .catch(function (error) {
            window.location.href = "/tournament-not-found.html";
        });
    },
    methods: {
        correctDate: function(timeStamp) {
            const currentDate = new Date();
            const timezoneOffset = currentDate.getTimezoneOffset() * 60;

            let date = new Date((timeStamp - timezoneOffset) * 1000);
            date = date.toUTCString();
            date = date.substring(0, (date.length - 7));

            return date;
        },
        correctDescription: function(description, markedEnable) {
            if (!description) {
                return '';
            }

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
        }
    }
};