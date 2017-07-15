const RegistrationApproval = {
    template: '#registration-approval-template',
    data: function() {
        return {
            loading: true,
            message: {
                success: '',
                error: ''
            }
        };
    },
    created: function() {
        const self = this;

        axios.get(`http://dev.api.pcesports.com/register/${this.$route.params.code}`)
        .then((response) => {
            self.message.success = response.data.message;
            pce.storage('set', 'token', { "sessionToken": response.data.sessionToken }, 604800000); // 7 days
            self.$parent.login();
            this.$parent.displayMessage('Registration successful', 'success');
            self.$router.push('/profile');
        })
        .catch((error) => {
            if (error.response.data.message) {
                self.message.error = error.response.data.message;
            } else {
                self.message.error = 'System error';
                console.log(error);
            }
            self.loading = false;
        });
    }
};