<template>
<div class="home">
    <div class="block tournaments-lists">
        <div class="block-content tournaments-to-watch">
            <h2>Tournaments to watch</h2>

            <loading v-if="tournamentsLoading"></loading>

            <div class="event-list-wrapper">
                <div v-if="watchTournaments.length"
                    v-for="game in watchTournaments"
                    :key="game.id"
                >
                    <div :class="{ 'live': game.live == 1 }"
                        class="watch-item"
                    >
                        <a :href="game.home_link"
                            target="_blank"
                            class="event-image"
                            rel="noopener"
                        >
                            <img :src="'/dist/assets/images/' + getGameAbbr(game.game) +'.png'" />
                        </a>

                        <a :href="game.home_link"
                            target="_blank"
                            class="event-name-info"
                            rel="noopener"
                        >
                            <h4 class="event-name" :title="game.name">{{game.name}}</h4>
                            <div>
                                <i class="fa fa-trophy"></i> {{game.prize}}
                            </div>
                        </a>

                        <div class="watch-dates">
                            <div><i class="fa fa-calendar"></i> <i class="fa fa-step-forward"></i> {{game.startTime}}</div>
                            <div><i class="fa fa-calendar"></i> <i class="fa fa-times"></i> {{game.endTime}}</div>
                        </div>

                        <div class="event-status-links">
                            <a v-if="game.live == 1"
                                :href="game.twitch_link"
                                target="_blank"
                                rel="noopener"
                                class="event-status"
                            ><i class="live"></i> LIVE NOW</a>

                            <div class="event-links">
                                <a :href="game.home_link"
                                    target="_blank"
                                    rel="noopener"
                                    title="External source link"
                                ><i class="fa fa-external-link"></i></a>

                                <a :href="game.twitch_link"
                                    target="_blank"
                                    rel="noopener"
                                    title="Twitch"
                                ><i class="fa fa-twitch"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="block-content tournaments-latest">
            <h2>Upcoming tournaments to play</h2>

            <loading v-if="tournamentsLoading"></loading>

            <div class="event-list-wrapper" v-if="upcomingTournaments.length">
                <event-item :game="game"
                    :key="game.id"
                    v-for="game in upcomingTournaments"></event-item>
            </div>
        </div>
    </div>

    <div class="block home-games-categories">
        <div class="block-content reddit-posts">
            <h2>Top topics per day from /r/esports</h2>

            <loading v-if="redditLoading"></loading>
            <div v-else-if="reddits"
                class="reddit-posts__wrapper"
            >
                <a v-for="post in reddits"
                    :key="post.redditUrl"
                    :href="`https://www.reddit.com${post.redditUrl}`"
                    target="_blank"
                    rel="noopener"
                    class="reddit-posts__post"
                >
                    <div class="reddit-posts__post__score">{{ post.karma }}</div>
                    <div :style="{'background-image': `url(${post.image})`}"
                        class="reddit-posts__post__image"
                    />
                    <div class="reddit-posts__post__links links-content">
                        <div class="links-content__title">{{ post.title }}</div>
                        <div class="links-content__external-url">
                            <a :href="post.externalUrl"
                                class="links-content__external-url__url"
                                target="_blank"
                                rel="noopener"
                            >{{ post.externalUrl }}</a>
                            <a :href="post.externalUrl"
                                class="fa fa-external-link"
                                target="_blank"
                                rel="noopener"
                            />
                        </div>
                    </div>
                    <!-- 
                        timePost: post.data.created_utc,
                        comments: post.data.num_comments-->
                </a>
            </div>
            <div v-else
                class="reddit-posts--no-posts"
            >No reddit topics to load ;(</div>
        </div>

        <div v-if="!loading"
            class="block-content tournaments-all"
        >
            <h2>Choose a game</h2>

            <div class="game-wrapper" v-if="!loading">
                <router-link
                    :to="game.link"
                    :key="game.link"
                    class="game-block"
                    v-for="game in games"
                    v-bind:class="game.cssClass"
                >
                    <span class="game-available-count">{{game.count}}</span>
                    <button class="btn btn-primary view-tournament">View {{game.gameName}} tournaments</button>
                </router-link>

                <router-link
                    :to="'../events'"
                    class="view-all btn btn-lg btn-secondary">View all tournaments</router-link>
            </div>
        </div>
    </div>

    <div class="block seo-about">
        <seo>
            <h1>Esports online tournaments list for PC games</h1>
            <p>"PC Esports" provide list of tournament events, played on PC, mostly online, for North America and Europe. Catalog created with the idea to help amateur and semi-pro teams to find tournaments easily on centralized platform. Catalog displaying informations for games such as <router-link :to="'../events/league-of-legends'">League of Legends</router-link>, <router-link :to="'../events/hearthstone'">Hearthstone</router-link> from many platforms such as <a href="http://events.na.leagueoflegends.com/" target="_blank">LoL Events page</a>, <a href="https://battlefy.com/" target="_blank">Battlefy</a>, <a href="https://strivewire.com/" target="_blank">StriveWire</a>, <a href="https://esportswall.com/" target="_blank">Eports Wall</a>, <a href="https://play.eslgaming.com" target="_blank">ESL</a>, <a href="https://www.toornament.com/" target="_blank">Toornament</a>.</p>
            <p>PC Esports is not in any way connected to tournament organizers specified in catalog. PC Esports is not responsible for organizers actions and updates that can happen to event.</p>
        </seo>
    </div>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Global functions
import { functions } from '../../functions.js';

// Components
import seo from '../../components/seo/seo.vue';
import loading from '../../components/loading/loading.vue';
import eventItem from '../../components/event-item/event-item.vue';

const homePage = {
    components: {
        seo,
        loading,
        eventItem
    },
    data: function() {
        return {
            loading: true,
            tournamentsLoading: true,
            redditLoading: true,
            reddits: [],
            games: [],
            upcomingTournaments: [],
            watchTournaments: [],
            gameOrder: ['lol', 'hs', 'ow', 'rl', 'dota', 'cs']
        };
    },
    created() {
        let games = [];

        for (const game of this.gameOrder) {
            const gameData = functions.getGameData(game);
            
            games.push({
                gameName: gameData.name,
                cssClass: 'game-' + gameData.abbriviature,
                link: '../events/' + gameData.link,
                abbriviature: gameData.abbriviature
            });
        }

        this.games = games;

        this.fetchTournaments();

        this.fetchReddits();

        const checkStorage = functions.storage('get', 'home-data');
        if (checkStorage) {
            for(const game of this.games) {
                game.count = checkStorage.tournamentsCount[game.abbriviature];
            }

            this.loading = false;
        }
        else {
            axios.all([
                axios.get(`${pce.apiUrl}/tournaments/count`)
            ])
            .then(axios.spread((
                tournamentsCountData
            ) => {
                for(const game of this.games) {
                    game.count = tournamentsCountData.data[game.abbriviature];
                }

                const homeData = {
                    tournamentsCount: tournamentsCountData.data
                };

                functions.storage('set', 'home-data', homeData);

                this.loading = false;
            }));
        }
    },
    methods: {
        async fetchReddits() {
            try {
                const response = await axios.get('https://www.reddit.com/r/esports/top/.json?limit=5&t=day');

                for (const post of response.data.data.children) {
                    console.log(post.data);

                    this.reddits.push({
                        title: post.data.title,
                        image: post.data.thumbnail,
                        redditUrl: post.data.permalink,
                        externalUrl: post.data.url,
                        timePost: post.data.created_utc,
                        karma: post.data.score,
                        comments: post.data.num_comments
                    });
                }
            } catch (error) {
                console.error(error);
            } finally {
                this.redditLoading = false;
            }
        },
        fetchTournaments: function() {
            axios.all([
                axios.get(`${pce.apiUrl}/tournaments?limit=5`),
                axios.get(`${pce.apiUrl}/tournaments/watch`)
            ])
            .then(axios.spread((
                tournamentsPlayData,
                tournamentsWatchData
            ) => {
                this.parsePlayTournaments(tournamentsPlayData);
                this.parseWatchTournaments(tournamentsWatchData);
                
                this.tournamentsLoading = false;
            }));
        },
        parsePlayTournaments: function(tournamentsPlayData) {
            let gamesFiltered = tournamentsPlayData.data;
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

            this.upcomingTournaments = gamesFiltered;
        },
        parseWatchTournaments: function(tournamentsWatchData) {
            let gamesFiltered = tournamentsWatchData.data;
            let currentDate = new Date();
            let timezoneOffset = currentDate.getTimezoneOffset() * 60;

            for (let i = 0; i < gamesFiltered.length; i++) {
                let startDate = new Date((gamesFiltered[i].start_time - timezoneOffset) * 1000);
                let endDate = new Date((gamesFiltered[i].end_time - timezoneOffset) * 1000);

                startDate = startDate.toUTCString();
                endDate = endDate.toUTCString();

                gamesFiltered[i].startTime = startDate.substring(0, (startDate.length - 13));
                gamesFiltered[i].endTime = endDate.substring(0, (endDate.length - 13));
            }

            this.watchTournaments = gamesFiltered;
        },
        getGameAbbr: function(game) {
            const gameAbbr = functions.getGameData(game);
            
            return gameAbbr.abbriviature;
        }
    }
};

// Routing
pce.routes.push({
    path: '/',
    component: homePage,
    meta: {
        title: 'Choose a game, League of Legends, Hearthstone, Dota2, CS:GO, Rocket League, Overwatch',
        description: 'PC Esports is a catalog of events for online competitive gaming.'
    }
});

export default homePage;
</script>