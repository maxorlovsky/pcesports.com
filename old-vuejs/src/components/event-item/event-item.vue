<template>
<div class="event-item"
    :class="{ 'editable': editable }"
>
    <router-link :to="link + '/' + getGameLink(game.game) + '/' + game.id"
        class="event-image"
    >
        <img :src="'/dist/assets/images/' + game.game +'.png'" />
    </router-link>

    <router-link :to="link + '/' + getGameLink(game.game) + '/' + game.id"
        class="event-name-info"
    >
        <h4 class="event-name" :title="game.name">{{game.name}}</h4>
        <div>
            <i class="fa fa-globe"></i> {{game.region}} | <i class="fa fa-calendar"></i> {{game.startTime}} | <i class="fa fa-users"></i> {{game.participantsLimit}}
        </div>
    </router-link>

    <div class="event-organizer">
        <img v-if="game.platform && game.platform != 'lol-event'"
            :src="'/dist/assets/images/tournament-platforms/' + game.platform + '.png'"
            :title="game.platformName"
            :alt="game.platformName"
        />
    </div>

    <div class="event-status-links">
        <div class="event-status" :class="game.eventStatus.toLowerCase()">{{game.eventStatus}}</div>
        <div class="event-links">
            <a :href="game.link" target="_blank" title="External source link"><i class="fa fa-external-link"></i></a>
            <a :href="game.fbLink" target="_blank" v-if="game.fbLink" title="Facebook"><i class="fa fa-facebook"></i></a>
            <a :href="game.ytLink" target="_blank" v-if="game.ytLink" title="Youtube"><i class="fa fa-youtube"></i></a>
            <a :href="game.twLink" target="_blank" v-if="game.twLink" title="Twitch"><i class="fa fa-twitch"></i></a>
            <a :href="game.dsLink" target="_blank" v-if="game.dsLink" title="Discord"><i class="fa fa-comments"></i></a>
        </div>
    </div>

    <div class="event-details"
        v-if="editable"
        :class="{ 'double-width': editable }"
    >
        <router-link :to="link + '/' + getGameLink(game.game) + '/' + game.id">
            <button class="btn btn-success">Edit</button>
        </router-link>
        <button class="btn btn-danger" v-if="editable">Delete</button>
    </div>

    <div class="event-details" v-else>
        <router-link :to="'/events/' + getGameLink(game.game) + '/' + game.id">
            <button class="btn btn-success">Details</button>
        </router-link>
    </div>
</div>
</template>

<script>
// Global functions
import { functions } from '../../functions.js';

export default {
    name: 'event-item',
    props: {
        game: {
            type: Object,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        }
    },
    data: function() {
        if (this.game.participantsLimit === '0') {
            this.game.participantsLimit = 'Unlimited';
        }

        return {
            link: this.editable ? '/event/add' : '/events'
        };
    },
    methods: {
        getGameLink: function(gameAbbriviature) {
            let game = functions.getGameData(gameAbbriviature);
            
            return game.link;
        }
    }
}
</script>