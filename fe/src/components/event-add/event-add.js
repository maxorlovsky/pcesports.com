const EventAdd = {
    template: '#event-add-template',
    data: function() {
        return {
            loading: true,
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
        if (!pce.loggedIn) {
            this.$parent.displayMessage('You must be logged in to enter this page', 'danger');
            this.$router.push('/');
            return false;
        }

        if (this.$route.params.addition) {
            this.editable = true;
        } else {
            this.addable = true;
            this.switchGame('lol');
        }

        this.hints = this.populateHints();
        this.years = this.populateYears();
        setTimeout(() => this.fullTextHeight(), 150);

        this.loading = false;
    },
    methods: {
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
        switchGame: function(game) {
            this.form.game = game;
            this.currentGame = pce.getGameData(game);
        },
        submitForm: function() {
            let self = this;

            this.formLoading = true;

            if (!this.form.name || !this.form.region || !this.form.prizes || !this.form.description  || !this.form.day || !this.form.month || !this.form.year || !this.form.time || !this.form.link || !this.form.participants_limit) {
                self.$parent.displayMessage('Please fill in the form', 'danger');
                this.formLoading = false;
                this.errorClasses = {
                    name: !this.form.name ? true : false,
                    region: !this.form.region ? true : false,
                    prizes: !this.form.prizes ? true : false,
                    description: !this.form.description ? true : false,
                    participants_limit: !this.form.participants_limit ? true : false,
                    day: !this.form.day ? true : false,
                    month: !this.form.month ? true : false,
                    year: !this.form.year ? true : false,
                    time: !this.form.time ? true : false,
                    link: !this.form.link ? true : false
                };

                return false;
            }

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
                self.$parent.displayMessage(response.data.message, 'success');
                const gameLink = pce.getGameData(self.form.game);
                const url = `/events/${gameLink.link}/${response.data.id}`;
                self.$router.push(url);
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