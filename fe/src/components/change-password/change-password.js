const ChangePassword = {
    template: '#change-password-template',
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
                oldPass: '',
                pass: '',
                repeatPass: ''
            },
            errorClasses: {
                oldPass: false,
                pass: false,
                repeatPass: false
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
                oldPass: false,
                pass: false,
                repeatPass: false
            };

            // Frontend check
            if (!this.form.oldPass || !this.form.pass || !this.form.repeatPass) {
                // Generic error message
                this.formError = 'Please fill in the form';
                this.formLoading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    oldPass: !this.form.oldPass ? true : false,
                    pass: !this.form.pass ? true : false,
                    repeatPass: !this.form.repeatPass ? true : false
                };

                return false;
            }

            axios.put(`${pce.apiUrl}/user-data/change-password`, {
                oldPass: this.form.oldPass,
                pass: this.form.pass,
                repeatPass: this.form.repeatPass
            })
            .then(function (response) {
                self.$parent.displayMessage(response.data.message, 'success');
                self.formLoading = false;
                self.form = {
                    oldPass: '',
                    pass: '',
                    repeatPass: ''
                };
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
    path: '/profile/change-password',
    component: ChangePassword,
    props: true,
    meta: {
        loggedIn: true,
        title: 'Change Password - Profile',
        template: 'change-password',
        description: 'User page to change password'
    }
});