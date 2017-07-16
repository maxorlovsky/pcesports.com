const Profile = {
    template: '#profile-template',
    data: function() {
        return {
            loading: true
        };
    },
    created: function() {
        this.loading = false;

        /* const self = this;

        axios.get(`http://dev.api.pcesports.com/register/${this.$route.params.code}`)
        .then((response) => {
            self.message.success = response.data.message;
            pce.storage('set', 'token', response.data.sessionToken, 604800000); // 7 days
            self.$parent.login();
            self.loading = false;
            self.$route.router.go('/profile'); 
        })
        .catch((error) => {
            self.message.error = error.response.data.message;
            self.loading = false;
        }); */
    }
};