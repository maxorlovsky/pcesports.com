Vue.component('header-component', {
    template: dynamicTemplates.header,
    props: {
        loggedIn: {
            type: 'boolean'
        }
    },
    data: function() {
        return {
            mood: '',
            logoSmall: false,
            burgerStatus: this.$parent.leftSideMenu,
            rightMenuStatus: this.$parent.rightSideMenu,
            menu: {},
            loggedIn: false
        };
    },
    created: function() {
        window.addEventListener('scroll', this.handleScroll);

        return this.fetchData();
    },
    destroyed: function() {
        window.removeEventListener('scroll', this.handleScroll);
    },
    watch: {
        $parent: function() {
            console.log('test');
        }
    },
    methods: {
        fetchData: function() {
            const month = new Date().getMonth();
            if (month == 11 || month < 1) {
                this.mood = 'winter';
            }

            this.menu = this.$parent.menu;
        },
        handleScroll: function() {
            if (window.scrollY !== 0) {
                this.logoSmall = true;
            }
            else {
                this.logoSmall = false;
            }
        },
        burgerMenu: function() {
            this.$emit('nav-menu');
        },
        openRightMenu: function(form) {
            this.$emit('right-menu');
        }
    }
});