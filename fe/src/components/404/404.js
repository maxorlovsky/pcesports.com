const PageNotFound = {
    template: '#page-not-found-template',
    data: function () {
        window.location.href = "../404";
        return {};
    }
};

// Routing
pce.routes.push({
    path: '/404',
    component: PageNotFound,
    meta: {
        title: 'Page not found',
        template: '404'
    }
});