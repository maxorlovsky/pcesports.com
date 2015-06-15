</section>

<footer class="container">
    <div class="copyrights">
        <p>Pentaclick Widget.</p>
    </div>
</footer>

<? if (_cfg('env') == 'dev') { ?>
<script src="<?=_cfg('static')?>/js/widget.js"></script>
<? } else { ?>
<script src="<?=_cfg('static')?>/js/widget-combined.js"></script>
<? } ?>

</section>

</body>
</html>