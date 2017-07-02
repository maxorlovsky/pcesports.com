Vue.component('right-side-menu-component', {
    template: dynamicTemplates.rightSideMenu,
    props: {
        loggedIn: {
            type: 'boolean'
        }
    },
    data: function() {
        return {
            loggedIn: false
        };
    },
    methods: {
        triggerClick: function() {
            this.$emit('right-menu');
        },
        logout: function() {
            let self = this;

            // Exiting in both cases
            axios.post('https://api.pcesports.com/logout')
            .then(function (response) {
                self.$emit('logout');
            })
            .catch(function (error) {
                self.$emit('logout');
            });
        }
    }
});