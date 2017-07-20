const Profile = {
    template: '#profile-template',
    props: {
        loggedIn: {
            type: 'boolean'
        },
        userData: {
            type: 'object'
        }
    },
    data: function() {
        return {
            loading: true,
            user: {}
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
        }
    }
};