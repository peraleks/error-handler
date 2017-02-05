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
        var wraps = document.querySelectorAll('.trace_wrap');
        for (var i = 0; i < wraps.length; i++) {
            wraps[i].addEventListener('click', function (e) {
                    console.log(e.target);
                    var target = e.target.querySelector('.tooltip_wrap');
                    console.log(target);
                    if (null != target) {
                        target.classList.toggle('hidden');
                    }
                }
            );
        }
    }
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