<template>
<footer>
    <p class="copyrights">© 2014 - {{year}} PCEsports.com / <a href="https://maxorlovsky.com" target="_blank">Maxtream</a>.</p>
    <div class="footer-links">
        <a href="https://twitter.com/pcesports"><i class="fa fa-twitter"></i></a>
        <a href="https://www.youtube.com/user/pentaclickesports"><i class="fa fa-youtube"></i></a>
        <!--<a href="../pentaclick">About PC Esports</a> | 
        <a href="../contacts">Contacts</a>-->
    </div>
</footer>
</template>

<script>
export default {
    name: 'footer-component',
    data: function() {
        const year = new Date().getFullYear();
        
        return {
            year: year,
        };
    }
}
</script>