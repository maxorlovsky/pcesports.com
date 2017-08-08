Vue.component('events-filters-component', {
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
            filters: {},
            searchString: '',
            searchStatus: false
        };
    },
    created: function() {
        
    },
    methods: {
        updateFilter: function() {
            this.filters = {
                region: this.region,
                status: this.status,
                searchString: this.searchString,
                currentGame: this.currentGame,
            };

            this.$emit('update-filter', this.filters);
        },
        cleanSearch: function(keyAction) {
            const self = this;

            if (keyAction && !this.searchString) {
                this.searchStatus = false;
                return false;
            }
            else if (keyAction) {
                this.searchStatus = true;
                return false;
            }
            
            if (this.searchString.length === 0 && this.searchStatus) {
                // Need to delay the function call
                setTimeout(() => {
                    self.updateFilter();
                });
            }
        }
    }
});