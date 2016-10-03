<section class="container page faq">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('faq')?></h1>
        </div>

        <div class="block-content">
            <div class="text">
                <?
                if ($this->faq) {
                    $i = 1;
                    foreach($this->faq as $v) {
                    
                ?>
                <div class="QnA">
                    <div class="question"><?=$i?>. <?=$v->question?></div>
                    <div class="answer"><?=$v->answer?></div>
                </div>
                <?
                    ++$i;
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
$('.QnA').on('click', function() {
    $(this).find('.answer').stop().slideToggle('fast');
});
</script>