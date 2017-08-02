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
            form: {
                game: 'lol'
            },
            errorClasses: {},
            gamesList: ['lol', 'hs', 'ow', 'hots', 'rl', 'dota', 'cs', 'smite'],
            months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            days: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'],
            years: [],
            hints: {}
        };
    },
    created: function() {
        const self = this;

        if (this.$route.params.addition) {
            if (this.$route.params.addition === 'edit' && pce.loggedIn) {
                this.editable = true;
            }
            else if (this.$route.params.addition === 'add' && pce.loggedIn) {
                this.addable = true;
            }
            else {
                // In case if not logged in, not edit/add and still addition is there, redirect
                const path = this.$route.path.substring(0, this.$route.path.length - this.$route.params.addition.length - 1);
                this.$router.push(path);
            }
        }

        if (this.addable) {
            this.switchGame('lol');
            this.years = this.populateYears();
            setTimeout(() => this.fullTextHeight(), 150);
            this.loading = false;
        } else {
            this.currentGame = pce.getGameData(this.$route.params.game);
            this.eventId = parseInt(this.$route.params.event);

            axios.get(`${pce.apiUrl}/tournament/${this.currentGame.abbriviature}/${this.eventId}`)
            .then(function (response) {
                self.prepareView(response);
                
                if (self.editable) {
                    self.prepareEditForm();
                }

                self.loading = false;
            })
            .catch(function (error) {
                window.location.href = "/tournament-not-found.html";
            });
        }
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
                description: 'In this event formatting is set as HTML, if you edit your event, formatting is going to be changed to "wiki/reddit markup" format.'
            };
        },
        populateYears: function() {
            const years = [
                new Date().getFullYear(),
                (new Date().getFullYear() + 1)
            ];

            return years;
        },
        switchGame: function(game) {
            this.form.game = game;
            this.currentGame = pce.getGameData(game);
        },
        prepareView: function(response) {
            this.game = response.data;

            this.game.name = this.game.name
                .replace(/&amp;/g, "&")
                .replace(/&gt;/g, ">")
                .replace(/&lt;/g, "<")
                .replace(/&quot;"/g, "\"");

            // Set up meta title
            document.title += ' - '+ this.game.name;

            this.game.meta_data = JSON.parse(this.game.meta_data);

            // Set up meta description
            const cutDownDescription = this.game.meta_data.description.substring(0, 100);
            const metaDescription = `${this.game.name} | ${cutDownDescription}...`;
            document.querySelector('meta[name="description"]').setAttribute("content", metaDescription);

            // Settings those sooner, before they will be changed, for edit form
            if (this.editable) {
                this.form.prizes = this.game.meta_data.prizes;
                this.form.description = this.game.meta_data.description;
            }

            this.game.meta_data.prizes = this.game.meta_data.prizes.replace(/(?:\r\n|\r|\n)/g, '<br />');
            if (this.game.platform === 'battlefy' && (this.game.game === 'ow' || this.game.game === 'hots')) {
                this.game.meta_data.description = this.correctDescription(this.game.meta_data.description, false);
                this.form.formatting = true;
                this.form.description = this.game.meta_data.description;
            } else if (this.game.platform === 'esl') {
                this.game.meta_data.description = this.correctDescription(this.game.meta_data.description, false);
                this.form.formatting = true;
                this.form.description = this.game.meta_data.description;
            } else {
                this.game.meta_data.description = this.correctDescription(this.game.meta_data.description, true);
            }
            this.game.platform = {
                name: this.game.platformName,
                image: this.game.platformName === 'Custom' ? false : this.game.platform
            };
            this.game.start_datetime = this.correctDate(this.game.start_datetime);
            this.game.meta_data.elimination = this.correctEventFormat(this.game.meta_data.elimination);
            if (this.game.meta_data.free) {
                this.game.meta_data.free = this.correctPayment(this.game.meta_data.free);
            }
            if (this.game.meta_data.online) {
                this.game.meta_data.online = this.correctOnline(this.game.meta_data.online);
            }

            if (this.game.participants_limit === '0') {
                this.game.participants_limit = 'Unlimited';
            }
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
        },
        submitForm: function() {
            let self = this;

            this.formLoading = true;

            /* if (!this.form.name || !this.form.region || !this.form.prizes || !this.form.description  || !this.form.day || !this.form.month || !this.form.year || !this.form.time || !this.form.link) {
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
                    time: !this.form.time ? true : false,
                    link: !this.form.link ? true : false
                };

                return false;
            } */

            axios.post(`${pce.apiUrl}/tournament/add`, {
                name: this.form.name,
                game: this.form.game,
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
                self.formLoading = false;

                self.$parent.displayMessage(error.response.data.message, 'danger');
                
                let errorFields = error.response.data.fields;

                // In some cases slim return array as json, we need to convert it
                if (errorFields.constructor !== Array) {
                    errorFields = Object.keys(errorFields).map(key => errorFields[key]);
                }

                // Mark fields with error class
                self.errorClasses = {};
                for (let i = 0; i < errorFields.length; ++i) {
                    self.errorClasses[errorFields[i]] = true;
                }
            });
        }
    }
};