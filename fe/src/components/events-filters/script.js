Vue.component('events-filters', {
    template: dynamicTemplates.eventsFilters,
    props: {
        region: {
            type: 'String'
        },
        status: {
            type: 'String'
        },
        searchString: {
            type: 'String'
        },
        currentGame: {
            type: 'String'
        }
    },
    data: function() {
        return {
            region: this.region,
            status: this.status,
            searchString: this.searchString,
            currentGame: this.currentGame
        };
    },
    created: function() {
        
    },
    methods: {
        
    }
});