<?php $id = 'wrap'.rand(); ?>
<style>
    <?= $style ?>
</style>
<div class="error_box" style="font-size: <?= $fontSize ?>px">
    <div class="<?= $cssType ?> error_header">
        <?= '['.$code.'] '.$type ?>
        <span class="handler"> (<?= $handler ?>)</span>
    </div>
    <div class="error_text error_content">
        <?= $message ?>
    </div>
    <div id="<?= $id ?>" class="trace_wrap <?= $hidden ?>">
        <?= $trace ?>
    </div>
    <div class="error_path error_content">
        <?php if ($trace != '') : ?>
        <div class="but_trace" onclick = "parentNode.previousElementSibling.classList.toggle('hidden')">
            trace
        </div>
        <?php endif; ?>
        <?= $file ?>
        <div class="error_path error_content error_line"><?= $line ?></div>
        <span class="app_dir"><?= $path ?></span>
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
