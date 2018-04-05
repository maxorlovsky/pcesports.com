<template>
<div class="events-filters">
    <input class="filter-input"
        type="text"
        placeholder="Input name of event and press enter"
        v-model="searchString"
        v-on:keyup.enter="searchString.length >= 3 ? updateFilter() : false"
        v-on:keydown.8="cleanSearch(true)"
        v-on:keydown.46="cleanSearch(true)"
        v-on:keyup.8="cleanSearch(false)"
        v-on:keyup.46="cleanSearch(false)"
    />

    <select class="form-control form-control-lg btn-secondary"
        v-model="status.current"
        v-on:change="updateFilter()"
    >
        <option :value="value"
            :key="value"
            v-for="value in status.list"
        >{{status.name}}: {{value}}</option>
    </select>

    <select class="form-control form-control-lg btn-primary"
        v-model="region.current"
        v-on:change="updateFilter()"
    >
        <option :value="key"
            :key="value"
            v-for="(value, key) in region.list"
        >{{region.name}}: {{value}}</option>
    </select>
</div>
</template>

<script>
export default {
    name: 'events-filters',
    props: {
        region: {
            type: Object
        },
        status: {
            type: Object
        },
        searchString: {
            type: String
        },
        currentGame: {
            type: Object
        }
    },
    data: function() {
        return {
            filters: {},
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
}
</script>