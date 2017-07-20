Vue.component('forgot-password', {
    template: dynamicTemplates.forgotPassword,
    data: function() {
        return {
            restoreError: '',
            restoreSuccess: '',
            captchaId: 0,
            captchaKey: '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z',
            loading: false,
            showForm: 1,
            form: {
                login: ''
            },
            resetForm: {
                code: '',
                pass: '',
                repeatPass: ''
            },
            errorClasses: {
                login: false,
                code: false,
                pass: false,
                repeatPass: false
            }
        };
    },
    mounted() {
        if (window.grecaptcha) {
            this.captchaId = grecaptcha.render( document.getElementById('fp-g-recaptcha'), { sitekey : this.captchaKey });
        }
    },
    methods: {
        submit: function() {
            let self = this;

            this.loading = true;
            this.restoreSuccess = '';
            this.restoreError = '';
            this.errorClasses.login = false;

            if (!this.form.login) {
                this.restoreError = 'Please fill in the form';
                this.loading = false;
                this.errorClasses.login = true;
                return false;
            }

            axios.post(`${pce.apiUrl}/forgot-password`, {
                login: this.form.login,
                captcha: grecaptcha.getResponse(this.captchaId)
            })
            .then(function (response) {
                self.restoreSuccess = response.data.message;
                self.showForm = 2;
                self.loading = false;
                self.checkCaptcha();
            })
            .catch(function (error) {
                self.restoreError = error.response.data.message;
                self.loading = false;
                self.errorClasses.login = true;
                self.checkCaptcha();
            });
        },
        submitReset: function() {
            let self = this;

            this.loading = true;
            this.restoreSuccess = '';
            this.restoreError = '';
            this.errorClasses = {
                login: false,
                code: false,
                pass: false,
                repeatPass: false
            };

            if (!this.resetForm.code || !this.resetForm.pass || !this.resetForm.repeatPass) {
                this.restoreError = 'Please fill in the form';
                this.loading = false;
                this.errorClasses = {
                    login: true,
                    code: !this.form.code ? true : false,
                    pass: !this.form.pass ? true : false,
                    repeatPass: !this.form.repeatPass ? true : false
                };
                return false;
            }

            axios.post(`${pce.apiUrl}/forgot-password/reset`, {
                login: this.form.login,
                code: this.resetForm.code,
                pass: this.resetForm.pass,
                repeatPass: this.resetForm.repeatPass
            })
            .then(function (response) {
                pce.storage('set', 'token', { "sessionToken": response.data.sessionToken }, 604800000); // 7 days
                self.$parent.$emit('right-menu');
                self.$parent.$parent.login();
                self.$parent.$parent.displayMessage(response.data.message, 'success');
                self.$router.push('/profile');
            })
            .catch(function (error) {
                self.restoreError = error.response.data.message;
                self.loading = false;

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
        },
        openLoginMenu: function() {
            this.$parent.$parent.rightSideMenuForm = 'login';
            this.$emit('right-menu');
        },
        checkCaptcha: function() {
            if (!document.getElementById('fp-g-recaptcha').innerHTML) {
                this.captchaId = grecaptcha.render( document.getElementById('fp-g-recaptcha'), { sitekey : this.captchaKey });
            } else {
                grecaptcha.reset();
            }
        }
    }
});