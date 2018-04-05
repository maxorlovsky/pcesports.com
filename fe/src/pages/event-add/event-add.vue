<template>
<div class="event-add editable">
    <div class="block">
        <div class="block-content">
            <loading v-if="loading"></loading>

            <div class="event-details-header" v-if="!loading && game">
                <textarea class="game-name"
                    placeholder="Event name *"
                    v-on:keyup="fullTextHeight()"
                    v-model="form.name"
                    :class="{ error: errorClasses.name }"></textarea>

                <div v-if="addable" class="games-list">
                    <div class="games-list-block"
                        :class="{ picked: form.game === gameIcon }"
                        v-for="gameIcon in gamesList"
                        :key="gameIcon"
                        v-on:click="switchGame(gameIcon)">
                        <img :src="'../../../dist/assets/images/' + gameIcon + '.png'" />
                    </div>
                </div>
                <div v-else class="game-image">
                    <img :src="'../../../dist/assets/images/' + game.game +'-logo-big.png'" />
                </div>
            </div>

            <div class="event-details-wrapper" v-if="!loading && game">
                <div class="event-details-data">
                    <div class="event-details-params">
                        <p v-if="game.platform" class="event-details-platform">
                            <label>Platform: <span class="fa fa-exclamation-circle" v-tooltip="hints.platform"></span></label>
                            {{game.platform.name}} 
                            <img v-if="game.platform.image"
                                :src="'/dist/assets/images/tournament-platforms/' + game.platform.image + '.png'" />
                        </p>
                        <p>
                            <label>Region *:</label>
                            <select v-model="form.region" :class="{ error: errorClasses.region }">
                                <option
                                    v-for="(value, key) in currentGame.regions"
                                    :value="key"
                                    :key="value"
                                    v-if="key !== 'all'">{{value}}</option>
                                <option>Other</option>
                            </select>
                            </p>
                        <p>
                            <label>Participants limit *:</label>
                            <input type="number"
                                v-model="form.participants_limit"
                                :class="{ error: errorClasses.participants_limit }" />
                        </p>
                        <p class="edit-start-date">
                            <label>Start date *:</label>
                            <span class="date-wrapper">
                                <select v-model="form.day" :class="{ error: errorClasses.day }">
                                    <option v-for="i in days" :key="i">{{i}}</option>
                                </select>
                                <select v-model="form.month" :class="{ error: errorClasses.month }">
                                    <option v-for="i in months" :key="i">{{i}}</option>
                                </select>
                                <select v-model="form.year" :class="{ error: errorClasses.year }">
                                    <option v-for="i in years" :key="i">{{i}}</option>
                                </select>
                            </span>
                        </p>
                        <p>
                            <label>Best of:</label> 
                            <input type="text"
                                v-model="form.best_of"
                                :class="{ error: errorClasses.best_of }" />
                        </p>
                        <p>
                            <label>Start time *: <span class="fa fa-exclamation-circle" v-tooltip="hints.time"></span></label>
                            <input type="text"
                                v-model="form.time"
                                :class="{ error: errorClasses.time }" />
                        </p>
                        <p>
                            <label>Tournament format:</label> 
                            <input type="text"
                                v-model="form.elimination"
                                :class="{ error: errorClasses.elimination }" />
                        </p>
                        <p>
                            <label>Payment:</label>
                            <select v-model="form.free" :class="{ error: errorClasses.free }">
                                <option></option>
                                <option>Free</option>
                                <option>Payment required</option>
                            </select>
                        </p>
                        <p>
                            <label>Online:</label>
                            <select v-model="form.online" :class="{ error: errorClasses.online }">
                                <option></option>
                                <option>Yes</option>
                                <option>No</option>
                            </select>
                        </p>
                    </div>

                    <div class="event-additiona-data">
                        <div class="event-prizes">
                            <h4>Prizes</h4>
                            <textarea
                                v-model="form.prizes"
                                class="textarea-prizes"
                                v-on:keyup="fullTextHeight()"
                                :class="{ error: errorClasses.prizes }"></textarea>
                        </div>

                        <div class="event-links">
                            <h4>Links</h4>
                            <div class="event-link-wrapper">
                                <i class="fa fa-external-link"></i> Link to platform
                                <input type="text"
                                    v-model="form.link"
                                    :class="{ error: errorClasses.link }" />
                            </div>
                            <div class="event-link-wrapper">
                                <i class="fa fa-external-link"></i> Home page
                                <input type="text"
                                    v-model="form.home_link"
                                    :class="{ error: errorClasses.home_link }" />
                            </div>
                            <div class="event-link-wrapper">
                                <i class="fa fa-external-link"></i> Registration link
                                <input type="text"
                                    v-model="form.registration_link"
                                    :class="{ error: errorClasses.registration_link }" />
                            </div>
                            <div class="event-link-wrapper">
                                <i class="fa fa-facebook"></i> Facebook
                                <input type="text"
                                    v-model="form.facebook_link"
                                    :class="{ error: errorClasses.facebook_link }" />
                            </div>
                            <div class="event-link-wrapper">
                                <i class="fa fa-youtube"></i> YouTube
                                <input type="text"
                                    v-model="form.youtube_link"
                                    :class="{ error: errorClasses.youtube_link }" />
                            </div>
                            <div class="event-link-wrapper">
                                <i class="fa fa-twitch"></i> Twitch.TV
                                <input type="text"
                                    v-model="form.twitch_link"
                                    :class="{ error: errorClasses.twitch_link }" />
                            </div>
                            <div class="event-link-wrapper">
                                <i class="fa fa-comments"></i> Discord chat
                                <input type="text"
                                    v-model="form.discord_link"
                                    :class="{ error: errorClasses.discord_link }" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="event-description editable">
                    <h4>
                        Event description
                        <span class="fa fa-exclamation-circle"
                            v-if="form.formatting && editable"
                            v-tooltip="hints.description"></span>
                    </h4>
                    <textarea
                        v-model="form.description"
                        class="textarea-description"
                        v-on:keyup="fullTextHeight()"
                        :class="{ error: errorClasses.description }"></textarea>
                </div>
            </div>

            <div class="buttons-wrapper">
                <div class="btn-wrapper-width" v-if="addable">
                    <button
                        class="btn btn-success btn-lg add-btn"
                        v-on:click="submitForm()">Add tournament</button>
                </div>
                <div class="btn-wrapper-width" v-if="editable">
                    <button
                        class="btn btn-success btn-lg edit-btn"
                        v-on:click="submitEditForm()">Edit tournament</button>
                </div>
                <router-link :to="'/tournaments'">
                    <button class="btn btn-primary btn-lg back-btn">Back to list</button>
                </router-link>
            </div>
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

const eventAddPage = {
    components: {
        loading
    },
    data: function() {
        return {
            loading: true,
            game: {},
            currentGame: {},
            addable: false,
            editable: false,
            form: {
                game: 'lol'
            },
            errorClasses: {},
            gamesList: ['lol', 'hs', 'ow', 'hots', 'rl', 'dota', 'cs'],
            months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            days: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'],
            years: [],
            hints: {}
        };
    },
    created: function() {
        if (!pce.loggedIn) {
            this.$parent.displayMessage('You must be logged in to enter this page', 'danger');
            this.$router.push('/');
            return false;
        }

        if (this.$route.params.addition) {
            this.editable = true;
        } else {
            this.addable = true;
            this.switchGame('lol');
        }

        this.hints = this.populateHints();
        this.years = this.populateYears();
        setTimeout(() => this.fullTextHeight(), 150);

        this.loading = false;
    },
    methods: {
        fullTextHeight: function() {
            const textareas = document.querySelectorAll('textarea');

            for (let textarea of textareas) {
                textarea.style.height = 0;
                textarea.style.height = `${textarea.scrollHeight}px`;
            }
        },
        populateHints: function() {
            return {
                platform: 'If you update this tournament it will be moved from current platform to "Custom"',
                description: 'In this event formatting is set as HTML, if you edit your event, formatting is going to be changed to "wiki/reddit markup" format.',
                time: 'Time must be in format either 14:30 or 2:30PM'
            };
        },
        populateYears: function() {
            const years = [
                new Date().getFullYear(),
                (new Date().getFullYear() + 1)
            ];

            return years;
        },
        switchGame: function(game) {
            this.form.game = game;
            this.currentGame = functions.getGameData(game);
        },
        submitForm: function() {
            this.formLoading = true;

            if (!this.form.name || !this.form.region || !this.form.prizes || !this.form.description  || !this.form.day || !this.form.month || !this.form.year || !this.form.time || !this.form.link || !this.form.participants_limit) {
                this.$parent.displayMessage('Please fill in the form', 'danger');
                this.formLoading = false;
                this.errorClasses = {
                    name: !this.form.name ? true : false,
                    region: !this.form.region ? true : false,
                    prizes: !this.form.prizes ? true : false,
                    description: !this.form.description ? true : false,
                    participants_limit: !this.form.participants_limit ? true : false,
                    day: !this.form.day ? true : false,
                    month: !this.form.month ? true : false,
                    year: !this.form.year ? true : false,
                    time: !this.form.time ? true : false,
                    link: !this.form.link ? true : false
                };

                return false;
            }

            axios.post(`${pce.apiUrl}/tournament/add`, {
                name: this.form.name,
                game: this.form.game,
                region: this.form.region,
                participants_limit: parseInt(this.form.participants_limit),
                best_of: this.form.best_of,
                elimination: this.form.elimination,
                free: this.form.free,
                online: this.form.online,
                day: this.form.day,
                month: this.form.month,
                year: this.form.year,
                time: this.form.time,
                link: this.form.link,
                home_link: this.form.home_link,
                registration_link: this.form.registration_link,
                facebook_link: this.form.facebook_link,
                youtube_link: this.form.youtube_link,
                twitch_link: this.form.twitch_link,
                discord_link: this.form.discord_link,
                prizes: this.form.prizes,
                description: this.form.description
            })
            .then((response) => {
                this.$parent.displayMessage(response.data.message, 'success');
                const gameLink = functions.getGameData(this.form.game);
                const url = `/events/${gameLink.link}/${response.data.id}`;
                this.$router.push(url);
            })
            .catch((error) => {
                this.formLoading = false;

                this.$parent.displayMessage(error.response.data.message, 'danger');
                
                let errorFields = error.response.data.fields;

                // In some cases slim return array as json, we need to convert it
                if (errorFields.constructor !== Array) {
                    errorFields = Object.keys(errorFields).map(key => errorFields[key]);
                }

                // Mark fields with error class
                this.errorClasses = {};
                for (let i = 0; i < errorFields.length; ++i) {
                    this.errorClasses[errorFields[i]] = true;
                }
            });
        }
    }
};

// Routing
pce.routes.push({
    path: '/event/add/:game?/:event?',
    component: eventAddPage,
    meta: {
        title: 'Event Add',
        description: 'Event Add'
    }
});

export default eventAddPage;
</script>