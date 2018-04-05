<template>
<header :class="mood">
    <div class="fa fa-bars burger"
        v-on:click="burgerMenu();"
        :class="{ 'active': burgerStatus }"
    ></div>

    <router-link :to="'/'" class="logo" :class="{ 'logo-small': logoSmall }">
        <img src="/dist/assets/images/logo.png" />
    </router-link>

    <nav>
        <ul>
            <li :class="'nav-link ' + link.css_classes"
                :key="link.url"
                v-for="link in menu"
            >
                <a v-if="link.target" :href="link.url" :target="link.target">{{link.title}}</a>
                <router-link v-else :to="link.url" :target="link.target" exact>{{link.title}}</router-link>

                <ul class="nav-sub" v-if="link.sublinks">
                    <li :class="'nav-sublink ' + sublink.css_classes"
                        :key="sublink.url"
                        v-for="sublink in link.sublinks"
                    >
                        <a v-if="sublink.target" :href="sublink.url" :target="sublink.target">{{sublink.title}}</a>
                        <router-link v-else :to="sublink.url" :target="sublink.target">{{sublink.title}}</router-link>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

     <div class="nav-user" v-if="!loggedIn">
        <button class="btn btn-primary login-button" v-on:click="openRightMenu('login')">Login</button>
        <button class="btn btn-success register-button" v-on:click="openRightMenu('register')">Sign up</button>
    </div>
    <div class="nav-user" v-else>
        <button class="btn btn-info fa fa-user" v-on:click="openRightMenu()"></button>
    </div> 

    <div v-if="mood">
        <ul class="snow">
            <li></li>
            <li></li>
        </ul>
        <div class="lights"></div>
    </div>
</header>
</template>

<script>
export default {
    name: 'header-component',
    props: {
        loggedIn: {
            type: Boolean
        }
    },
    data: function() {
        return {
            mood: '',
            logoSmall: false,
            burgerStatus: this.$parent.leftSideMenu,
            rightMenuStatus: this.$parent.rightSideMenu,
            menu: {}
        };
    },
    created: function() {
        window.addEventListener('scroll', this.handleScroll);

        return this.fetchData();
    },
    destroyed: function() {
        window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
        fetchData: function() {
            const month = new Date().getMonth();
            
            if (month == 11 || month < 1) {
                this.mood = 'winter';
            }

            this.menu = this.$parent.menu;
        },
        handleScroll: function() {
            if (window.scrollY !== 0) {
                this.logoSmall = true;
            }
            else {
                this.logoSmall = false;
            }
        },
        burgerMenu: function() {
            this.$emit('nav-menu');
        },
        openRightMenu: function(form) {
            this.$parent.rightSideMenuForm = form;
            this.$emit('right-menu');
        }
    }
}
</script>