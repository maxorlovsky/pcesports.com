<div class="home">
    
    <div class="block blog-home-page">
        <div class="block-header-wrapper">
            <h1 class="">Latest blogs</h1>
        </div>

        <div class="block-content">
            <div class="small-blog-block" v-for="post in posts">
                <a href="<?=_cfg('href')?>/blog/<?=$v->id?>" class="blog-link-wrapper">
                    <div class="image-holder">
                        <span v-if="post.image" v-html="post.image"></span>
                        <p v-else>No image</p>
                    </div>
                    <h4 class="title">{{post.title.rendered}}</h4>
                    <div class="dates" v-html="post.date"></div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
axios.get('/wp/wp-json/wp/v2/posts/?per_page=3')
.then(function (response) {
    posts = response.data;

    for (i = 0; i < posts.length; ++i) {
        date = (new Date(posts[i].date));
        posts[i].date = date.toLocaleString('en-us', { month: "short" })+'<br />'+date.getDate();
    }

    new Vue({
        el: '.blog-home-page',
        data: {
            posts: posts,
        }
    });
});

</script>