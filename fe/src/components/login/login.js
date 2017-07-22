Vue.component('login', {
    template: dynamicTemplates.login,
    data: function() {
        return {
            login: '',
            pass: '',
            loginError: '',
            loading: false,
            formLoading: false
        };
    },
    methods: {
        submit: function() {
            let self = this;

            this.formLoading = true;

            if (!this.login || !this.pass) {
                this.loginError = 'Please fill in the form';
                this.formLoading = false;
                return false;
            }

            axios.post(`${pce.apiUrl}/login`, {
                login: this.login,
                pass: this.pass
            })
            .then(function (response) {
                pce.storage('set', 'token', response.data, 604800000); // 7 days
                self.$parent.$parent.login();
                self.formLoading = false;
            })
            .catch(function (error) {
                self.loginError = error.response.data.message;
                self.formLoading = false;
            });
        },
        openForgotPassMenu: function() {
            this.$parent.$parent.rightSideMenuForm = 'forgot-pass';
            this.$emit('right-menu');
        }
    }
});