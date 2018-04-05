<template>
<div class="events">
    <div class="block">
        <div class="block-content">
            <loading v-if="loading"></loading>

            <h2>{{currentGame.name}} tournaments</h2>

            <events-filters
                :status="status"
                :region="region"
                :search-string="searchString"
                :current-game="currentGame"
                v-on:update-filter="fetchEventData"
            ></events-filters>

            <div class="event-table-notification alert alert-info">Table can be scrolled horizontally</div>
            <div class="event-list-wrapper" v-if="games.length">
                <event-item :game="game"
                    :key="game.id"
                    v-for="game in games"></event-item>
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

    <div class="block seo-about">
        <seo v-html="seoText"></seo>
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
import eventItem from '../../components/event-item/event-item.vue';
import eventsFilters from '../../components/events-filters/events-filters.vue';
import seo from '../../components/seo/seo.vue';

const eventsPage = {
    components: {
        loading,
        eventsFilters,
        eventItem,
        seo
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
            hideLoadMore: true,
            limit: 40,
            offset: 0,
            loadingMore: false,
            seoText: ''
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
        fetchEventData: function(filters = {}) {
            this.loading = true;

            if (Object.keys(filters).length > 0) {
                this.status = filters.status;
                this.region = filters.region;
                this.searchString = filters.searchString;
                this.currentGame = filters.currentGame;
            }

            let filter = '?';

            if (this.$route.params.game) {
                this.currentGame = functions.getGameData(this.$route.params.game);
                if (!this.currentGame.abbriviature) {
                    this.$router.push('/404');
                    return false;
                }

                filter += '&game=' + this.currentGame.abbriviature;
                this.region.list = this.currentGame.regions;

                // Set custom made meta description
                const metaDescription = `Find all competitive ${this.currentGame.name} tournaments around North America and Europe. Find tournaments of a different skill level that will suit your team or you personally.`;
                
                functions.setUpCustomMeta(this.currentGame.name, metaDescription);
            } else {
                this.currentGame.name = 'All';
            }

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

            this.seoText = this.loadSeo(this.$route.params.game);
        },
        loadMore: function() {
            this.loadingMore = true;
            this.offset += this.limit;

            this.fetchEventData();
        },
        loadSeo: function(game) {
            let text = '';

            switch(game) {
                case 'dota':
                    text = '<h1>Upcoming Dota 2 tournaments</h1>\
                    <h2>List of Competitive Dota 2 tournaments</h2>\
                    <p>Find all competitive Dota tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'counter-strike':
                    text = '<h1>Upcoming Counter Strike tournaments</h1>\
                    <h2>List of Competitive Counter Strike tournaments</h2>\
                    <p>Find all competitive Counter Strike "CSGO" tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'rocket-league':
                    text = '<h1>Upcoming Rocket League tournaments</h1>\
                    <h2>List of Competitive Rocket League tournaments</h2>\
                    <p>Find all competitive Rocket League tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'heroes-of-the-storm':
                    text = '<h1>Upcoming Heroes of the Storm tournaments</h1>\
                    <h2>List of Competitive Heroes of the Storm tournaments</h2>\
                    <p>Find all competitive Heroes of the Storm tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'league-of-legends':
                    text = '<h1>Upcoming League of Legends tournaments</h1>\
                    <h2>List of Competitive League of Legends tournaments</h2>\
                    <p>Find all competitive League of Legends tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'hearthstone':
                    text = '<h1>Upcoming Hearthstone tournaments</h1>\
                    <h2>List of Competitive Hearthstone tournaments</h2>\
                    <p>Find all competitive Hearthstone tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                case 'overwatch':
                   text = '<h1>Upcoming Overwatch tournaments</h1>\
                    <h2>List of Competitive Overwatch tournaments</h2>\
                    <p>Find all competitive Overwatch tournaments around North America and Europe.</p>\
                    <p>Find tournaments of a different skill level that will suit your team or you personally.</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
                default:
                    text = '<h1>List of Upcoming competitive gaming tournaments</h1>\
                    <p>Find all competitive gaming tournaments around North America and Europe.</p>\
                    <p>List include games like League of Legends, Hearthstone, Overwatch, Rocket League, Heroes of the Storm, Dota 2, Counter-Strike: Global Offensive, full list of what gamer might need</p>\
                    <p>Get your team to find the perfect competitive esports tournament for you all around North America or Europe region</p>\
                    <p>Tournaments are gathered from all around popular platforms and list provide you will the most relevant data and links that you might need to start you participation as fast and easy as possible</p>';
                break;
            }

            return text;
        }
    }
};

// Routing
pce.routes.push({
    path: '/events',
    component: eventsPage,
    meta: {
        title: 'Tournaments',
        description: 'Find all competitive gaming tournaments around North America and Europe. List include games like League of Legends, Hearthstone, Overwatch, Rocket League, Heroes of the Storm, Dota 2, Counter-Strike: Global Offensive, full list of what gamer might need'
    },
    children: [
        {
            path: ':game',
            meta: {
                title: 'tournaments'
            }
        }
    ]
});

export default eventsPage;
</script>