Vue.component('right-side-menu-component', {
    template: dynamicTemplates.rightSideMenu,
    data: function() {
        //console.log(this.$parent.loggedIn);
        return {
            loggedIn: this.$parent.loggedIn
        };
    },
    methods: {
        triggerClick: function() {
            this.$emit('right-menu');
        }
    }
});