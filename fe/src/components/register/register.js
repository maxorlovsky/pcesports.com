Vue.component('register', {
    template: dynamicTemplates.register,
    data: function() {
        return {
            name: '',
            login: '',
            pass: '',
            repeatPass: '',
            registerError: '',
            captcha: '',
            loading: false
        };
    },
    mounted() {
        if (window.grecaptcha) {
            this.rcapt_id = grecaptcha.render( document.getElementById('reg-g-recaptcha'), { sitekey : '6LcwJ_8SAAAAAL2SgH-NYduvEp9DLUlndHrlMs7Z' });
        }
    },
    methods: {
        submit: function() {
            let self = this;

            this.loading = true;

            if (!this.login || !this.pass || !this.repeatPass || !this.name) {
                this.registerError = 'Please fill in the form';
                this.loading = false;
                return false;
            }

            axios.post('http://dev.api.pcesports.com/register', {
                login: this.login,
                pass: this.pass
            })
            .then(function (response) {
                pce.storage('set', 'token', response.data, 604800000); // 7 days
                self.$parent.$parent.login();
                self.loading = false;
            })
            .catch(function (error) {
                self.registerError = error.response.data.message;
                self.loading = false;
            });
        }
    }
});