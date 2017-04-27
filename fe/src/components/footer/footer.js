Vue.component('footer-component', {
    template: dynamicTemplates.footer,
    data: function() {
        const year = new Date().getFullYear();
        
        return {
            year: year,
        };
    }
});