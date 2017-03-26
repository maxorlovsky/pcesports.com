let router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            component: Home,
            meta: {
                template: 'home'
            },
        },
        {
            path: '/blog',
            component: Blog,
            meta: {
                title: 'Blog',
                template: 'blog'
            },
            children: [
                {
                    path: 'page/:page',
                    meta: {
                        title: 'Blog',
                        template: 'blog'
                    }
                }
            ]
        },
        {
            path: '/article/:post',
            component: Article,
            meta: {
                title: 'Article',
                template: 'article'
            }
        },
        {
            path: '/events',
            component: Events,
            meta: {
                title: 'Events List',
                template: 'events'
            },
            children: [
                {
                    path: ':game',
                    meta: {
                        title: 'Events List',
                        template: 'events'
                    }
                }
            ]
        },
        {
            path: '/events/:game/:event',
            component: EventDetails,
            meta: {
                title: 'Event Details',
                template: 'event-details'
            }
        },
        { path: '/404', component: PageNotFound, meta: { title: 'Page not found', template: '404' } },
        { path: '*', redirect: '/404' }
    ]
});

router.beforeEach((to, from, next) => {
    window.scrollTo(0, 0);
    
    document.title = 'PC eSports';
    if (to.meta.title) {
        document.title += ' - ' + to.meta.title;
    }
    
    // Loading html template for component
    let element = document.getElementById('template-holder');

    axios.get('/dist/html/' + to.meta.template + '.html')
    .then(function(template) {
        element.innerHTML = template.data;

        next();
    });
});

axios.all([
    axios.get('/dist/html/header.html'),
    axios.get('/dist/html/footer.html'),
    axios.get('/dist/html/event-item.html'),
    //axios.get('/dist/html/events-filters.html'),
    axios.get('/dist/html/ga.html')
])
.then(axios.spread(function (headerTemplate, footerTemplate, eventItemTemplate, gaTemplate) {
    dynamicTemplates.header.appendChild(document.createTextNode(headerTemplate.data));
    dynamicTemplates.footer.appendChild(document.createTextNode(footerTemplate.data));
    dynamicTemplates.eventItem.appendChild(document.createTextNode(eventItemTemplate.data));
    //dynamicTemplates.eventsFilters.appendChild(document.createTextNode(eventsFiltersTemplate.data));
    dynamicTemplates.ga.appendChild(document.createTextNode(gaTemplate.data));

    new Vue({
        el: '#app',
        router: router
    });

    document.getElementById('loading').remove();
}));