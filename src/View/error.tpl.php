<?php $id = 'peraleks_wrap'.rand(); ?>
<br>
<style>
    <?= $style ?>
</style>
<div class="<?= $cssType ?> peraleks_error_box" style="font-size: <?= $fontSize ?>px">
    <div class="header" title="<?= $handler ?>">
        <?= $type.' ['.$code.']' ?>
    </div>
    <div class="text">
        <?= $message ?>
    </div>
    <div id="<?= $id ?>" class="peraleks_tw <?= $hidden ?>">
        <?= $trace ?>
    </div>
    <div class="file">
        <span title="<?= $path ?>"><?= $file ?></span><span class="bracket">(</span><span class="line"><?= $line ?></span><span class="bracket">)</span>
        <?php if ($trace != '') : ?>
            <div class="but_trace" onclick="parentNode.previousElementSibling.classList.toggle('hidden')">
                trace <?= $traceCount ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php if ($trace != '') : ?>
    <script>
        (function () {
            var wrap = document.getElementById('<?= $id ?>');
            wrap.addEventListener('click', function (e) {
                 var target = e.target.querySelector('.tooltip_wrap');
                if (null != target) {
                    target.classList.toggle('hidden');
                }
            })
        })();
    </script>
<?php endif; ?>
<br>