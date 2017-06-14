Vue.component('right-side-menu-component', {
    template: dynamicTemplates.leftSideMenu,
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