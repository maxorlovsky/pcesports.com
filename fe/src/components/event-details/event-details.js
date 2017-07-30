const EventDetails = {
    template: '#event-details-template',
    data: function() {
        return {
            loading: true,
            pageError: '',
            game: {},
            currentGame: {},
            addable: false,
            editable: false,
            form: {},
            gamesList: ['lol', 'hs', 'ow', 'hots', 'rl', 'dota', 'cs', 'smite'],
            months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            years: [],
            hints: {}
        };
    },
    created: function() {
        const self = this;

        this.currentGame = pce.getGameData(this.$route.params.game);
        this.eventId = parseInt(this.$route.params.event);
        if (this.$route.params.edit === 'edit' && pce.loggedIn) {
            this.editable = true;
        }

        this.years = this.populateYears();

        axios.get('https://api.pcesports.com/tournament/' + this.currentGame.abbriviature + '/' + this.eventId)
        .then(function (response) {
            self.game = response.data;

            self.game.name = self.game.name
                .replace(/&amp;/g, "&")
                .replace(/&gt;/g, ">")
                .replace(/&lt;/g, "<")
                .replace(/&quot;"/g, "\"");

            // Set up meta title
            document.title += ' - '+ self.game.name;

            self.game.meta_data = JSON.parse(self.game.meta_data);

            // Set up meta description
            const cutDownDescription = self.game.meta_data.description.substring(0, 100);
            const metaDescription = `${self.game.name} | ${cutDownDescription}...`;
            document.querySelector('meta[name="description"]').setAttribute("content", metaDescription);

            self.form.prizes = self.game.meta_data.prizes;
            self.form.description = self.game.meta_data.description;
            self.game.meta_data.prizes = self.game.meta_data.prizes.replace(/(?:\r\n|\r|\n)/g, '<br />');
            if (self.game.platform === 'battlefy' && (self.game.game === 'ow' || self.game.game === 'hots')) {
                self.game.meta_data.description = self.correctDescription(self.game.meta_data.description, false);
                self.form.formatting = true;
                self.form.description = self.game.meta_data.description;
            } else if (self.game.platform === 'esl') {
                self.game.meta_data.description = self.correctDescription(self.game.meta_data.description, false);
                self.form.formatting = true;
                self.form.description = self.game.meta_data.description;
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

            if (self.editable) {
                setTimeout(() => self.fullTextHeight(), 150);

                // Run function for edit form
                self.game.meta_data.free = self.correctPayment(self.game.meta_data.free);
                self.game.meta_data.online = self.correctOnline(self.game.meta_data.online);

                self.form.day = self.game.start_datetime.substring(5, 7);
                self.form.month = self.game.start_datetime.substring(8, 11);
                self.form.year = self.game.start_datetime.substring(12, 16);
                self.form.time = self.game.start_datetime.substring(17, self.game.start_datetime.length);

                self.hints = self.populateHints();
            }
        })
        /* .catch(function (error) {
            window.location.href = "/tournament-not-found.html";
        }) */;
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
        },
        fullTextHeight: function() {
            if (document.querySelector('.game-name')) {
                document.querySelector('.game-name').style.height = 0;

                const scrollHeigth = document.querySelector('.game-name').scrollHeight;
                document.querySelector('.game-name').style.height = `${scrollHeigth}px`;
            }

            if (document.querySelector('.textarea-prizes')) {
                document.querySelector('.textarea-prizes').style.height = 0;

                const scrollHeigth = document.querySelector('.textarea-prizes').scrollHeight;
                document.querySelector('.textarea-prizes').style.height = `${scrollHeigth}px`;
            }

            if (document.querySelector('.textarea-description')) {
                document.querySelector('.textarea-description').style.height = 0;
                
                const scrollHeigth = document.querySelector('.textarea-description').scrollHeight;
                document.querySelector('.textarea-description').style.height = `${scrollHeigth}px`;
            }
        },
        populateHints: function() {
            return {
                platform: 'If you update this tournament it will be moved from current platform to "Custom"',
                description: 'In this event formatting is set as HTML, if you edit your event, formatting is going to be changed to "wiki/reddit markup" format.'
            };
        },
        populateYears: function() {
            const years = [
                new Date().getFullYear(),
                (new Date().getFullYear() + 1)
            ];

            return years;
        }
    }
};