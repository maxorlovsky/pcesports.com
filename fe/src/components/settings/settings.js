const Settings = {
    template: '#settings-template',
    props: {
        userData: {
            type: 'object'
        }
    },
    data: function() {
        return {
            loading: true,
            formLoading: false,
            formError: '',
            form: {
                timestyle: null,
                subscription: false
            },
            errorClasses: {
                timestyle: false,
                subscription: false
            }
        };
    },
    watch: {
        'userData': function() {
            return this.fillUserData();
        }
    },
    created: function() {
        return this.fillUserData();  
    },
    methods: {
        fillUserData: function() {
            this.form.timestyle = this.userData.timestyle;
            this.form.subscription = this.userData.subscription.removed === "0" ? true : false;

            if (!this.userData) {
                this.$parent.authRequired();
            }

            this.loading = false;
        },
        submit: function() {
            let self = this;

            this.formLoading = true;
            this.formError = '';

            this.errorClasses = {
                timestyle: false,
                subscription: false
            };

            // Frontend check
            /*if (!this.form.timestyle || !this.form.subscription) {
                // Generic error message
                this.formError = 'Please fill in the form';
                this.formLoading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    timestyle: !this.form.timestyle ? true : false,
                    subscription: !this.form.subscription ? true : false
                };

                return false;
            }*/

            axios.put(`${pce.apiUrl}/user-data/settings`, {
                timestyle: this.form.timestyle,
                subscription: this.form.subscription
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

// Routing
pce.routes.push({
    path: '/profile/settings',
    component: Settings,
    props: true,
    meta: {
        loggedIn: true,
        title: 'Settings - Profile',
        template: 'settings',
        description: 'User page to change password and personal settings'
    }
});