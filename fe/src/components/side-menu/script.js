Vue.component('side-menu-component', {
    template: dynamicTemplates.sideMenu,
    data: function() {
        return {
            menu: this.$parent.menu
        };
    },
    methods: {
         triggerClick: function() {
            this.$emit('nav-menu');
         }
    }
});