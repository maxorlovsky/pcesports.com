const Profile = {
    template: '#profile-template',
    props: {
        userData: {
            type: 'object'
        }
    },
    data: function() {
        return {
            loading: true,
            formLoading: false,
            user: {},
            formSuccess: '',
            formError: '',
            errorClasses: {
                name: false,
                battletag: false,
                avatar: false
            }
        };
    },
    watch: {
        'userData': function() {
            return this.fillUserData();
        }
    },
    created: function() {
        this.fillUserData();
    },
    methods: {
        fillUserData: function() {
            this.user = this.userData;

            if (!this.user) {
                this.$parent.authRequired();
            }

            this.loading = false;
        },
        submit: function() {
            let self = this;

            this.formLoading = true;
            this.formError = '';

            this.errorClasses = {
                name: false,
                battletag: false,
                avatar: false
            };

            // Frontend check
            if (!this.user.name || !this.user.battletag || !this.user.avatar) {
                // Generic error message
                this.formError = 'Please fill in the form';
                this.formLoading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    name: !this.user.name ? true : false,
                    battletag: false,
                    avatar: !this.user.avatar ? true : false
                };

                return false;
            }

            axios.put(`${pce.apiUrl}/user-data/profile`, {
                name: this.user.name,
                battletag: this.user.battletag,
                avatar: this.user.avatar
            })
            .then(function (response) {
                self.$parent.displayMessage(response.data.message, 'success');
                self.$parent.recacheLoggedInData();
                self.formLoading = false;
            })
            .catch(function (error) {
                self.formLoading = false;

                // Display error message from API
                self.formError = error.response.data.message;

                let errorFields = error.response.data.fields;

                // In some cases slim return array as json, we need to convert it
                if (errorFields.constructor !== Array) {
                    errorFields = Object.keys(errorFields).map(key => errorFields[key]);
                }

                // Mark fields with error class
                for (let i = 0; i < errorFields.length; ++i) {
                    self.errorClasses[errorFields[i]] = true;
                }
            });
        }
    },
};