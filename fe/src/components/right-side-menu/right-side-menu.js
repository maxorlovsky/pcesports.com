Vue.component('right-side-menu-component', {
    template: dynamicTemplates.rightSideMenu,
    data: function() {
        return {
            
        };
    },
    methods: {
        triggerClick: function() {
            this.$emit('right-menu');
        }
    }
});