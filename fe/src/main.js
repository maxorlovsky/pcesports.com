let router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/',
            component: Home,
            meta: {
                title: 'Home',
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
                        title: 'Blog page :page',
                        template: 'blog'
                    }
                }
            ]
        },
        {
            path: '/article/:post',
            component: Article,
            meta: {
                title: 'Article :post',
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
                        game: ':game',
                        title: 'Events list for :game',
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
    //window.scrollTo(0, 0);
    //document.title = to.meta.title;
    //next();

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
    axios.get('/dist/html/footer.html')
])
.then(axios.spread(function (headerTemplate, footerTemplate, rightBlockTemplate) {
    dynamicTemplates.header.appendChild(document.createTextNode(headerTemplate.data));
    dynamicTemplates.footer.appendChild(document.createTextNode(footerTemplate.data));

    new Vue({
        el: '#app',
        router: router
    });

    document.getElementById('loading').remove();
}));