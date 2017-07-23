const User = {
    template: '#user-template',
    data: function() {
        return {
            loading: true,
            userError: '',
            user: {},
        };
    },
    created: function() {
        const self = this;

        const userName = this.$route.params.name;

        axios.get(`${pce.apiUrl}/user-data/${userName}`)
        .then(function (response) {
            self.user = response.data.user;

            // Set up meta title
            document.title += ` - User Profile - ${self.user.name}`;

            self.loading = false;
        })
        .catch(function (error) {
            self.userError = error.response.data.message;
            self.loading = false;
        });
    },
    methods: {
        
    }
};