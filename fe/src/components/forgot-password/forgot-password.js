Vue.component('forgot-password', {
    template: dynamicTemplates.forgotPassword,
    data: function() {
        return {
            login: '',
            restoreError: '',
            restoreSuccess: '',
            rcapt_id: 0,
            captchaKey: '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z',
            loading: false
        };
    },
    mounted() {
        if (window.grecaptcha) {
            this.rcapt_id = grecaptcha.render( document.getElementById('fp-g-recaptcha'), { sitekey : this.captchaKey });
        }
    },
    methods: {
        submit: function() {
            let self = this;

            this.loading = true;
            this.restoreSuccess = '';
            this.restoreError = '';

            if (!this.login) {
                this.restoreError = 'Please fill in the form';
                this.loading = false;
                return false;
            }

            axios.post(`${pce.apiUrl}/forgot-password`, {
                login: this.login,
                captcha: grecaptcha.getResponse(this.rcapt_id)
            })
            .then(function (response) {
                self.restoreSuccess = response.data.message;
                self.loading = false;
            })
            .catch(function (error) {
                self.restoreError = error.response.data.message;
                self.loading = false;
                grecaptcha.reset();
            });
        },
        openLoginMenu: function() {
            this.$parent.$parent.rightSideMenuForm = 'login';
            this.$emit('right-menu');
        }
    }
});