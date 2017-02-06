const router = new VueRouter({
    mode: 'history',
    routes: [
        { path: '/', component: Home, meta: { title: 'Home' } },
        { path: '/blog', component: Blog, meta: { title: 'Blog' } },
        { path: '/blog/page/:page', component: Blog, meta: { title: 'Blog' } },
        //{ path: '/blog/article/:post', component: BlogArticle, meta: { title: '' } },
        { path: '*', component: Blog, meta: { title: 'Blog' } },
    ]
});

//let headerTemplate = '';

router.beforeEach((to, from, next) => {
    //window.scrollTo(0, 0);
    //document.title = to.meta.title;
    //next();

    // Loading html template for component
    let element = document.getElementById('template-holder');
    let getPath = to.path.split('/');
    getPath = getPath[1];

    if (getPath === '') {
        getPath = 'home';
    }

    axios.get('./dist/html/' + getPath + '.html')
    .then(function(template) {
        //headerTemplate = document.createElement('script');
        //headerTemplate.appendChild(template.data);
        
        element.innerHTML = template.data;

        next();
    });
});

axios.get('./dist/html/header.html')
.then(function(template) {
    let element = document.getElementById('template-holder');
    element.innerHTML = template.data;
    new Vue({
        el: '#header'
    });
});


new Vue({
    el: '#app',
    router: router
});