<template>
<div class="error-404 block">
    <div class="maintenance block-content">
        <h2>Page not found</h2>
        <img src="/dist/assets/images/maintenance.png" />
        <router-link :to="'/'">
            <button class="btn btn-primary btn-lg">Go to home page</button>
        </router-link>
    </div>
</div>
</template>

<script>
// 3rd party libs
import axios from 'axios';

// Global functions
import { functions } from '../../functions.js';

const pageNotFound = {
    data: function () {
        window.location.href = "../404.html";
        return {};
    }
};

// Routing
pce.routes.push({
    path: '/404',
    component: pageNotFound,
    meta: {
        title: 'Page not found',
        description: 'Page not found'
    }
});

export default pageNotFound;
</script>