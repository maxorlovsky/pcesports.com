const EventDetails = {
    template: '#event-details-template',
    data: function() {
        return {
            loading: false,
            game: {
                meta_data: {}
            },
            currentGame: {}
        };
    },
    mounted: function() {
        const self = this;

        this.currentGame = pce.getGameData(this.$route.params.game);
        this.eventId = parseInt(this.$route.params.event);

        axios.get(`${pce.apiUrl}/tournament/${this.currentGame.abbriviature}/${this.eventId}`)
        .then(function (response) {
            self.game = self.prepareView(response);

            if (self.editable) {
                self.prepareEditForm();
            }

            self.loading = false;
        })
        .catch(function (error) {
            self.$router.push('/404');
            return false;
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
                    fullName = 'Single Elimination';
                break;
                case 'double-elim':
                    fullName = 'Double Elimination';
                break;
                default:
                    fullName = format;
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
            const textareas = document.querySelectorAll('textarea');

            for (let textarea of textareas) {
                textarea.style.height = 0;
                textarea.style.height = `${textarea.scrollHeight}px`;
            }
        },
        populateHints: function() {
            return {
                platform: 'If you update this tournament it will be moved from current platform to "Custom"',
                description: 'In this event formatting is set as HTML, if you edit your event, formatting is going to be changed to "wiki/reddit markup" format.',
                time: 'Time must be in format either 14:30 or 2:30PM'
            };
        },
        populateYears: function() {
            const years = [
                new Date().getFullYear(),
                (new Date().getFullYear() + 1)
            ];

            return years;
        },
        prepareView: function(response) {
            const game = response.data;

            game.name = game.name
                .replace(/&amp;/g, "&")
                .replace(/&gt;/g, ">")
                .replace(/&lt;/g, "<")
                .replace(/&quot;"/g, "\"");

            // Set up meta title
            document.title += ' - '+ game.name;

            game.meta_data = JSON.parse(game.meta_data);

            // Set up meta description
            const cutDownDescription = game.meta_data.description.substring(0, 100);
            const metaDescription = `${game.name} | ${cutDownDescription}...`;
            document.querySelector('meta[name="description"]').setAttribute("content", metaDescription);

            // Settings those sooner, before they will be changed, for edit form
            if (this.editable) {
                this.form.prizes = game.meta_data.prizes;
                this.form.description = game.meta_data.description;
            }

            game.meta_data.prizes = game.meta_data.prizes.replace(/(?:\r\n|\r|\n)/g, '<br />');
            if (game.platform === 'battlefy' && (game.game === 'ow' || game.game === 'hots')) {
                game.meta_data.description = this.correctDescription(game.meta_data.description, false);
                if (this.editable) {
                    this.form.formatting = true;
                    this.form.description = game.meta_data.description;
                }
            } else if (game.platform === 'esl') {
                game.meta_data.description = this.correctDescription(game.meta_data.description, false);
                if (this.editable) {
                    this.form.formatting = true;
                    this.form.description = game.meta_data.description;
                }
            } else {
                game.meta_data.description = this.correctDescription(game.meta_data.description, true);
            }

            game.platform = {
                name: game.platformName,
                image: game.platformName === 'Custom' ? false : game.platform
            };
            
            game.start_datetime = this.correctDate(game.start_datetime);
            game.meta_data.elimination = this.correctEventFormat(game.meta_data.elimination);
            if (game.meta_data.free) {
                game.meta_data.free = this.correctPayment(game.meta_data.free);
            }
            if (game.meta_data.online) {
                game.meta_data.online = this.correctOnline(game.meta_data.online);
            }

            if (game.participants_limit === '0') {
                game.participants_limit = 'Unlimited';
            }

            return game;
        },
        prepareEditForm: function() {
            this.form.name = this.game.name;
            this.form.region = this.game.region;
            this.form.participants_limit = this.game.participants_limit;
            this.form.best_of = this.game.meta_data.best_of;
            this.form.elimination = this.game.meta_data.elimination;
            this.form.free = this.correctPayment(this.game.meta_data.free);
            this.form.online = this.correctOnline(this.game.meta_data.online);

            this.form.day = this.game.start_datetime.substring(5, 7);
            this.form.month = this.game.start_datetime.substring(8, 11);
            this.form.year = this.game.start_datetime.substring(12, 16);
            this.form.time = this.game.start_datetime.substring(17, this.game.start_datetime.length);

            this.form.link = this.game.link;
            this.form.home_link = this.game.meta_data.home_link;
            this.form.registration_link = this.game.meta_data.registration_link;
            this.form.facebook_link = this.game.meta_data.facebook_link;
            this.form.youtube_link = this.game.meta_data.youtube_link;
            this.form.twitch_link = this.game.meta_data.twitch_link;
            this.form.discord_link = this.game.meta_data.discord_link;

            this.hints = this.populateHints();
            this.years = this.populateYears();
            setTimeout(() => this.fullTextHeight(), 150);
        },
        submitEditForm: function() {
            let self = this;

            this.formLoading = true;
            this.errorClasses = {};

            if (!this.form.name || !this.form.region || !this.form.prizes || !this.form.description  || !this.form.day || !this.form.month || !this.form.year || !this.form.time) {
                self.$parent.displayMessage('Please fill in the form', 'danger');
                this.formLoading = false;
                this.errorClasses = {
                    name: !this.form.name ? true : false,
                    region: !this.form.region ? true : false,
                    prizes: !this.form.prizes ? true : false,
                    description: !this.form.description ? true : false,
                    day: !this.form.day ? true : false,
                    month: !this.form.month ? true : false,
                    year: !this.form.year ? true : false,
                    time: !this.form.time ? true : false
                };

                return false;
            }

            axios.post(`${pce.apiUrl}/tournament/edit`, {
                name: this.form.name,
                region: this.form.region,
                participants_limit: parseInt(this.form.participants_limit),
                best_of: this.form.best_of,
                elimination: this.form.elimination,
                free: this.form.free,
                online: this.form.online,
                day: this.form.day,
                month: this.form.month,
                year: this.form.year,
                time: this.form.time,
                link: this.form.link,
                home_link: this.form.home_link,
                registration_link: this.form.registration_link,
                facebook_link: this.form.facebook_link,
                youtube_link: this.form.youtube_link,
                twitch_link: this.form.twitch_link,
                discord_link: this.form.discord_link,
                prizes: this.form.prizes,
                description: this.form.description
            })
            .then(function (response) {
                /* self.restoreSuccess = response.data.message;
                self.showForm = 2;
                self.formLoading = false;
                self.checkCaptcha(); */
            })
            .catch(function (error) {
                self.formError = error.response.data.message;
                self.formLoading = false;
                self.errorClasses.login = true;
            });
        }
    }
};

// Routing
pce.routes.push({
    path: '/events/:game/:event',
    component: EventDetails,
    meta: {
        title: 'Event Details',
        template: 'event-details'
    }
});