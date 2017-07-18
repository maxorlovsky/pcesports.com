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

            if (!this.form.login) {
                this.restoreError = 'Please fill in the form';
                this.loading = false;
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
                self.checkCaptcha();
            });
        },
        submitReset: function() {
            let self = this;

            this.loading = true;
            this.restoreSuccess = '';
            this.restoreError = '';

            if (!this.resetForm.code || !this.resetForm.pass || !this.resetForm.repeatPass) {
                this.restoreError = 'Please fill in the form';
                this.loading = false;
                return false;
            }

            axios.post(`${pce.apiUrl}/forgot-password/reset`, {
                login: this.form.login,
                code: this.resetForm.code,
                pass: this.resetForm.pass,
                repeatPass: this.resetForm.repeatPass
            })
            .then(function (response) {
                self.restoreSuccess = response.data.message;
                self.showForm = 0;
                self.loading = false;
            })
            .catch(function (error) {
                self.restoreError = error.response.data.message;
                self.loading = false;
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