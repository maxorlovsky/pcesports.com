Vue.component('register', {
    template: dynamicTemplates.register,
    data: function() {
        return {
            form: {
                name: '',
                login: '',
                pass: '',
                repeatPass: ''
            },
            registerError: '',
            registerSuccess: '',
            loading: false,
            rcapt_id: 0,
            captchaKey: '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z',
            errorClasses: {
                name: false,
                login: false,
                pass: false,
                repeatPass: false,
                captcha: false
            }
        };
    },
    mounted() {
        if (window.grecaptcha) {
            this.rcapt_id = grecaptcha.render( document.getElementById('reg-g-recaptcha'), { sitekey : this.captchaKey });
        }
    },
    methods: {
        submit: function() {
            let self = this;

            this.loading = true;

            this.errorClasses = {
                name: false,
                login: false,
                pass: false,
                repeatPass: false,
                captcha: false
            };

            // Frontend check
            if (!this.form.login || !this.form.pass || !this.form.repeatPass || !this.form.name) {
                // Generic error message
                this.registerError = 'Please fill in the form';
                this.loading = false;

                // Mark specific fields as empty ones
                this.errorClasses = {
                    name: !this.form.name ? true : false,
                    login: !this.form.login ? true : false,
                    pass: !this.form.pass ? true : false,
                    repeatPass: !this.form.repeatPass ? true : false,
                    captcha: false
                };
                return false;
            }

            const recaptcha_response = grecaptcha.getResponse(this.rcapt_id);

            axios.post('http://dev.api.pcesports.com/register', {
                name: this.form.name,
                login: this.form.login,
                pass: this.form.pass,
                repeatPass: this.form.repeatPass,
                captcha: recaptcha_response
            })
            .then(function (response) {
                self.registerSuccess = response.data.message;
                self.loading = false;
            })
            .catch(function (error) {
                self.loading = false;

                // Display error message from API
                self.registerError = error.response.data.message;

                // Update recaptcha on error, new data required every time
                grecaptcha.reset();

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
    }
});