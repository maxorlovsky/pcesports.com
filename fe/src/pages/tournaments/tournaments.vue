<template>
<div class="tournaments">
    <div class="block">
        <div class="block-content">
            <loading v-if="loading"></loading>

            <h2>My tournaments</h2>

            <div class="heading-wrapper">
                <div class="heading-text">Here you can add, edit or delete tournaments added by you</div>
                <router-link :to="'/event/add'">
                    <button class="btn btn-success">Add new tournament</button>
                </router-link>
            </div>

            <!--<events-filters-component
                :status="status"
                :region="region"
                :search-string="searchString"
                :current-game="currentGame"
                v-on:update-filter="fetchEventData"
            ></events-filters-component>-->

            <!-- <div class="events-filters">
                <input class="filter-input"
                    type="text"
                    placeholder="Input name of event and press enter"
                    v-model="searchString"
                    v-on:keyup.enter="searchString.length >= 3 ? fetchEventData() : false"
                    v-on:keydown.8="cleanSearch(true)"
                    v-on:keydown.46="cleanSearch(true)"
                    v-on:keyup.8="cleanSearch(false)"
                    v-on:keyup.46="cleanSearch(false)"
                        />

                <select class="form-control form-control-lg btn-secondary"
                    v-model="status.current"
                    v-on:change="fetchEventData()">
                    <option :value="value" v-for="value in status.list">
                            {{status.name}}: {{value}}
                    </option>
                </select>

                <select class="form-control form-control-lg btn-primary"
                    v-model="region.current"
                    v-on:change="fetchEventData()">
                    <option :value="key" v-for="(value, key) in region.list">
                            {{region.name}}: {{value}}
                    </option>
                </select>
            </div>-->

            <div class="event-table-notification alert alert-info">Table can be scrolled horizontally</div>
            <div class="event-list-wrapper" v-if="games.length">
                <event-item
                    :game="game"
                    :editable="true"
                    :key="game.id"
                    v-for="game in games"
                ></event-item>
            </div>
            <div class="event-list-wrapper" v-else>
                <div class="event-none col-12">There are no tournaments tournaments matching criteria</div>
            </div>

            <button class="load-more btn btn-lg btn-primary"
                v-on:click="loadMore()"
                v-if="!hideLoadMore"
                :disabled="loadingMore">Load more</button>
        </div>
    </div>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Global functions
import { functions } from '../../functions.js';

// Components
import loading from '../../components/loading/loading.vue';

const tournamentsPage = {
    components: {
        loading
    },
    data: function() {
        return {
            loading: true,
            games: {},
            currentGame: {},
            status: {
                name: 'Status',
                list: ['All', 'Started', 'Starting soon', 'Upcoming'],
                current: 'All'
            },
            region: {
                name: 'Region',
                list: {
                    'all': 'All',
                    'na': 'North America',
                    'eu': 'Europe'
                },
                current: 'all'
            },
            searchString: '',
            searchStatus: false,
            hideLoadMore: true,
            limit: 40,
            offset: 0,
            loadingMore: false
        };
    },
    created: function() {
        return this.fetchEventData();
    },
    watch: {
        $route: function() {
            this.searchString = '';
            this.offset = 0;
            this.fetchEventData();
        }
    },
    methods: {
        fetchEventData: function() {
            this.loading = true;

            if (!pce.loggedIn) {
                this.$parent.displayMessage('You must be logged in to enter this page', 'danger');
                this.$router.push('/');
                return false;
            }
            
            let filter = '?';

            if (this.$route.params.game) {
                this.currentGame = pce.getGameData(this.$route.params.game);
                filter += '&game=' + this.currentGame.abbriviature;
                this.region.list = this.currentGame.regions;

                const metaDescription = `Find all competitive ${this.currentGame.name} tournaments around North America and Europe. Find tournaments of a different skill level that will suit your team or you personally.`;

                functions.setUpCustomMeta(' - ' + this.currentGame.name, metaDescription);
            } else {
                this.currentGame.name = 'All';
            }

            // Enable to display only user tournaments with problems
            filter += '&user=1&problems=1';

            filter += '&limit=' + this.limit;

            if (this.region.current && this.region.current != 'All') {
                filter += '&region='+this.region.current.toLowerCase();
            }

            if (this.status.current && this.status.current != 'All') {
                filter += '&status='+this.status.current.toLowerCase();
            }

            if (this.searchString && this.searchString.length >= 3) {
                filter += '&search='+this.searchString;
            }

            if (this.offset) {
                filter += '&offset=' + this.offset;
            }

            axios.get(`${pce.apiUrl}/tournaments${filter}`)
            .then((response) => {
                let gamesFiltered = response.data;
                let currentDate = new Date();
                let timezoneOffset = currentDate.getTimezoneOffset() * 60;

                for (let i = 0; i < gamesFiltered.length; i++) {
                    let date = new Date((gamesFiltered[i].startTime - timezoneOffset) * 1000);

                    gamesFiltered[i].name = gamesFiltered[i].name
                        .replace(/&amp;/g, "&")
                        .replace(/&gt;/g, ">")
                        .replace(/&lt;/g, "<")
                        .replace(/&quot;"/g, "\"");
                    date = date.toUTCString();
                    gamesFiltered[i].startTime = date.substring(0, (date.length - 7));
                }

                if (this.offset) {
                    let combinedArray = this.games.concat(gamesFiltered);
                    this.games = combinedArray;
                }
                else {
                    this.games = gamesFiltered;
                }

                if (gamesFiltered.length < this.limit || response.data.message === 'no-events') {
                    this.hideLoadMore = true;
                }
                else {
                    this.hideLoadMore = false;
                }

                this.loading = false;
                this.loadingMore = false;
            });
        },
        cleanSearch: function(keyAction) {
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
                    this.fetchEventData();
                });
            }
        },
        loadMore: function() {
            this.loadingMore = true;
            this.offset += this.limit;

            this.fetchEventData();
        }
    }
};

// Routing
pce.routes.push({
    path: '/tournaments',
    component: tournamentsPage,
    meta: {
        loggedIn: true,
        title: 'Tournaments List',
        description: 'User page to add, update and remove tournaments'
    }
});

export default tournamentsPage;
</script>