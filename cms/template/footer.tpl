<? if ($this->logged_in) { ?>
<div id="fader"></div>
<div id="hint"></div>
<div id="loading"><img src="<?=_cfg('cmsimg')?>/loading.gif" /></div>
<div id="aerrmsg"></div>
<div id="asucmsg"></div>
<div id="amsg"></div>

<script src="<?=_cfg('cmsstatic')?>/js/jquery-ui.min.js"></script>
<script src="../cms/vendor/tinymce/tinymce/tinymce.min.js"></script>
<? if (_cfg('env') == 'dev') { ?>
<script src="<?=_cfg('cmsstatic')?>/js/ajaxupload.js"></script>
<script src="<?=_cfg('cmsstatic')?>/js/main.js"></script>
<? } else { ?>
<script src="<?=_cfg('cmsstatic')?>/js/combined.js"></script>
<? } ?>

<? } ?>

</body>
</html>