module.exports = {
    rewrite: [
        { from: '/api/*', to: 'https://api.pcesports.com/$1' },
        { from: '/wp-api/*', to: 'https://api.pcesports.com/wp/wp-json/$1' },
    ],
    directory: 'public',
    spa: 'index.html',
    https: true,
    port: 8080
}