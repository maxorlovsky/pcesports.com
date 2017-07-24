Vue.component('right-side-menu-component', {
    template: dynamicTemplates.rightSideMenu,
    props: {
        loggedIn: {
            type: 'boolean'
        },
        form: {
            type: 'string',
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
            form: '',
            menu: {},
            userData: {}
        };
    },
    watch: {
        'menu': function() {
            return this.addUserNameToMenu();
        },
        'userData': function() {
            return this.addUserNameToMenu();
        }
    },
    created: function() {
        this.addUserNameToMenu();
    },
    methods: {
        triggerClick: function() {
            this.$emit('right-menu');
        },
        logout: function() {
            const self = this;

            // Exiting in both cases
            axios.post(`${pce.apiUrl}/logout`)
            .then(function (response) {
                self.$emit('logout');
            })
            .catch(function (error) {
                self.$emit('logout');
            });
        },
        addUserNameToMenu: function() {
            if (this.menu) {
                for (let item in this.menu) {
                    if (this.menu[item].url.indexOf('/user/') !== -1) {
                        // this.menu[item].url = this.menu[item].url.replace(':user_id', this.userData.name);
                        // Custom hack, because after first change of :user_id, system can not replace it second time
                        this.menu[item].url = `/user/${this.userData.name}`;
                    }
                }
            }
        }
    }
});