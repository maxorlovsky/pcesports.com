Vue.component('right-side-menu-component', {
    template: dynamicTemplates.rightSideMenu,
    props: {
        loggedIn: {
            type: 'boolean'
        },
        menu: {
            type: 'object'
        },
        userData: {
            type: 'object'
        }
    },
    data: function() {
        return {
            loggedIn: false,
            menu: {},
            userData: {}
        };
    },
    methods: {
        triggerClick: function() {
            this.$emit('right-menu');
        },
        logout: function() {
            const self = this;

            // Exiting in both cases
            axios.post('http://dev.api.pcesports.com/logout')
            .then(function (response) {
                self.$emit('logout');
            })
            .catch(function (error) {
                self.$emit('logout');
            });
        }
    }
});