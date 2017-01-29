<style>
    <?= $style ?>
</style>
<script>
    window.onload = function () {
        var trace = document.querySelectorAll('.but_trace');
        for (var i = 0; i < trace.length; i++) {
            trace[i].onclick = function () {
                this.parentNode.parentNode.children[2].classList.toggle('hidden');
            };
        }
    };
</script>
<div class="error_box" style="font-size: <?= $fontSize ?>px">
    <div class="<?= $cssName ?> error_header">
        <?= '['.$code.'] '.$name ?>
        <span class="handler"> (<?= $handler ?>)</span>
    </div>
    <div class="error_text error_content">
        <?= $message ?>
    </div>
    <div class="trace_wrap <?= $hidden ?>">
        <?= $trace ?>
    </div>
    <div class="error_path error_content">
        <div class="but_trace">trace</div>
        <?= $file ?>
        <div class="error_path error_content error_line"><?= $line ?></div>
        <span class="app_dir"><?= $path ?></span>
    </div>
</div>