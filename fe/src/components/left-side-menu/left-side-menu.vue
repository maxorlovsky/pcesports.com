<template>
<aside class="left-side-menu">
    <nav v-on:click="triggerClick()">
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
    <div class="side-menu-cover" v-on:click="triggerClick()"><i class="fa fa-times burger-closer"></i></div>
</aside>
</template>

<script>
export default {
    name: 'left-side-menu-component',
    data: function() {
        return {
            menu: this.$parent.menu
        };
    },
    methods: {
        triggerClick: function() {
            this.$emit('nav-menu');
        }
    }
}
</script>