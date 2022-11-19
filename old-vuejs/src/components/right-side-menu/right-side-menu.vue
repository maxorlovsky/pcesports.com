<template>
<aside class="right-side-menu">
    <section class="right-side-wrapper">
        <login v-if="!loggedIn && (form === 'login' || !form) "></login>

        <forgot-password v-if="!loggedIn && form === 'forgot-pass'"></forgot-password>

        <register v-if="!loggedIn && form === 'register'"></register>
        
        <div class="right-side-logged-in-menu" v-if="loggedIn">
            <loading v-if="!menu"></loading>
            <nav v-else>
                <div class="nav-user" v-if="userData.avatar">
                    <div class="nav-avatar">
                        <img v-bind:src="`/dist/assets/images/avatar/${userData.avatar}.jpg`" alt="Avatar" />
                    </div>
                    <div class="nav-username">{{userData.name}}</div>
                    <p class="nav-points">Points: <span class="achievementsPoints">{{userData.experience}}</span></p>
                </div>
                
                <ul v-on:click="triggerClick()">
                    <li :class="'nav-link ' + link.css_classes"
                        :key="link.url"
                        v-for="link in menu"
                    >
                        <a v-if="link.target" :href="link.url" :target="link.target">{{link.title}}</a>
                        <router-link v-else :to="link.url" :target="link.target" exact>{{link.title}}</router-link>

                        <ul class="nav-sub" v-if="link.sublinks">
                            <li :class="'nav-sublink ' + sublink.css_classes"
                                :key="sublink.link"
                                v-for="sublink in link.sublinks"
                            >
                                <a v-if="sublink.target" :href="sublink.url" :target="sublink.target">{{sublink.title}}</a>
                                <router-link v-else :to="sublink.url" :target="sublink.target">{{sublink.title}}</router-link>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-link logout-link">
                        <a href="javascript:;" v-on:click="logout()">Logout</a>
                    </li>
                </ul>
            </nav>
        </div>
    </section>

    <div class="side-menu-cover" v-on:click="triggerClick()"><i class="fa fa-times burger-closer"></i></div>
</aside>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Components
import login from '../../components/login/login.vue';
import register from '../../components/register/register.vue';
import forgotPassword from '../../components/forgot-password/forgot-password.vue';

export default {
    name: 'right-side-menu-component',
    components: {
        login,
        register,
        forgotPassword
    },
    props: {
        loggedIn: {
            type: Boolean
        },
        form: {
            type: String
        },
        menu: {
            type: Object
        },
        userData: {
            type: Object
        }
    },
    data: function() {
        return {};
    },
    watch: {
        'menu': function() {
            return this.addUserNameToMenu();
        },
        'userData': function() {
            return this.addUserNameToMenu();
        }
    },
    created: function() {
        this.addUserNameToMenu();
    },
    methods: {
        triggerClick: function() {
            this.$emit('right-menu');
        },
        logout: function() {
            // Exiting in both cases
            axios.post(`${pce.apiUrl}/logout`)
            .then((response) => {
                this.$emit('logout');
            })
            .catch((error) => {
                this.$emit('logout');
            });
        },
        addUserNameToMenu: function() {
            if (this.menu) {
                for (let item in this.menu) {
                    if (this.menu[item].url.indexOf('/user/') !== -1) {
                        // this.menu[item].url = this.menu[item].url.replace(':user_id', this.userData.name);
                        // Custom hack, because after first change of :user_id, system can not replace it second time
                        this.menu[item].url = `/user/${this.userData.name}`;
                    }
                }
            }
        }
    }
}
</script>