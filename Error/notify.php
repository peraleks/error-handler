<?php
    if (defined('MICRO_DIR')) {
        $file = str_replace(MICRO_DIR.'/', '', $file);
    }
    if (defined('MICRO_ERROR_TRACE_COLLAPSE') && MICRO_ERROR_TRACE_COLLAPSE === false)
         { $collaps = ''; }
    else { $collaps = 'hidden';}

    if (defined('MICRO_DEVELOPMENT') && MICRO_DEVELOPMENT === true):
?>
<style>
    
    div.error_box div.WARNING, div.USER_WARNING, div.CORE_WARNING, div.COMPILE_WARNING
    {
        background-color: #ffaa00;
    }

    div.error_box div.ERROR, div.CORE_ERROR, div.COMPILE_ERROR, div.USER_ERROR, div.RECOVERABLE_ERROR,
        div.not_caught_Exception
    {
        background-color: #ff0000;
    }

    div.error_box .PARSE
    {
        background-color: #fa00ff;
    }

    div.error_box div.NOTICE, div.USER_NOTICE
    {
        background-color: #ffff99 !important;
        color: #888 !important;
        border-left: 1px solid #ddd;
        border-top: 1px solid #ddd;
        border-right: 1px solid #ddd;
    }

    div.error_box div.STRICT
    {
        background-color: #8892BF;
    }

    div.error_box div.DEPRECATED, div.USER_DEPRECATED
    {
        background-color: #c48c00;
    }

    div.error_box div.Micro_Exception
    {
        background-color: #00bc09;
    }

    div.error_box div.Unknown_Type_Error
    {
        background-color: #666;
    }

    div.error_box {
        font-family: Consolas, monospace;
        font-size: 97.5%;
        margin: 5px 0;
        border-radius: 5px;
        min-width: 100%;
        display: inline-block;
    }

    div.error_box div.error_header {
        font-size: 110%;
        font-weight: 500;
        padding: 5px;
        color: #fff;
        border-radius: 5px 5px 0 0;
    }

    div.error_box div.error_content {
        padding: 8px 15px;
    }

    div.error_box div.error_text {
        font-size: 115%;
        background-color: #aaa;
        color: #fff;
        text-shadow: 2px 2px 7px rgba(0, 0, 0, 0.4), 0 0 1px #555;
    }

    div.error_box div.error_path {
        font-weight: 600;
        font-size: 110%;
        color: #444;
        background-color: #ddd;
        border-radius: 0 0 5px 5px;
        text-shadow: 0px 0px 1px rgba(255, 255, 255, 0.7);
    }

    div.error_box div.error_line {
        display: inline-block;
        font-size: 100%;
        font-weight: 500;
        border-radius: 50%;
        color: #ffff7b;
        background-color: #bbb;
        padding: 0.05em 0.4em 0.1em;
        text-shadow: 2px 2px 7px rgba(0, 0, 0, 0.5);
    }


    div.error_box div.error_text span.error {
        padding: 2px 5px;
        background-color: #9b9b9b;
        border-radius: 4px;
        color: #ffff88;
    }

    div.error_box div.error_text span.warning {
        color: #ffff88;
    }

    div.error_box  div.but_trace {
        font-weight: 500;
        display: inline-block;
        border-radius: 4px;
        color: #fff;
        background-color: #cdcdcd;
        padding: 0.05em 0.4em 0.2em;
        text-shadow: 2px 2px 7px rgba(0, 0, 0, 0.5);
    }

    div.error_box  div.but_trace:hover {
        cursor: pointer;
    }

    /* -------------------- stack trace ----------------------- */

    div.trace_wrap table.micro_trace {
        font-size: inherit;
        font-size: 80%;
        background-color: #444;
        border-spacing: 0px;
        color: #eee;
        min-width: 100%;
    }

    div.trace_wrap tr:active {
        background-color: #000 !important;
    }

    div.trace_wrap {
        min-width: 100%;
    }

    div.trace_wrap tr{
        line-height: 1.8em;
    }

    div.trace_wrap tr.color1{
        background-color: #555;
    }

    div.trace_wrap td {
        white-space: nowrap;
        text-align: center;
    }

    div.trace_wrap td.trace_path {
        text-align: right;
        padding-left: 3px;
        color: #aff;
    }

    div.trace_wrap td.trace_file {
        color: #28ffff;
        text-align: left;
    }

    div.trace_wrap td.trace_line {
        color: #ffff7b;
        padding-left: 7px;
        padding-right: 7px;
    }

    div.trace_wrap td.trace_class, span.trace_class {
        text-align: left;
        color: #49ff3f;
    }

    div.trace_wrap td.trace_name_space, span.trace_name_space {
        text-align: right;
        padding-left: 5px;
        padding-right: 7px;
        color: #9cea9a;
    }

    div.trace_wrap td.trace_function {
        padding-left: 5px;
        padding-right: 5px;
        color: #ffdb70;
    }

    div.trace_wrap td.trace_args {
        border-left: 1px solid #333;
        padding-left: 3px;
        padding-right: 3px;
        color: #f9f9ca;
    }

    div.trace_wrap td.trace_args.object {
        color: #9cea9a;
    }

    div.trace_wrap td.trace_args.array {
        color: #ccc;
    }

    div.trace_wrap td.trace_args .end {
        color: #f55;
    }

    div.trace_wrap table.micro_trace .trace_func {
        color: #f981f3;
    }

    div.error_box .hidden {
        display: none !important;
    }
</style>
<script>
    window.onload = function(){
        var trace = document.querySelectorAll('.but_trace');
        for (var i = 0; i < trace.length; i++) {
            trace[i].onclick = function() {
                this.parentNode.parentNode.children[2].classList.toggle('hidden');
            };
        }
    };
</script>
    <div class="error_box">
        <div class="<?= $name ?> error_header"><?='['.$code.'] '.$name; ?></div>
        <div class="error_text error_content">
        <?= $message ?>
        </div>
        <div class="trace_wrap <?= $collaps ?>">
        <?= $this->traceResult['display'] ?>
        </div>
        <div class="error_path error_content">
            <div class="but_trace">trace</div>
            <?= $file ?>
            <div class="error_path error_content error_line"><?= $line ?></div>
        </div>
    </div>

<?php         
    endif;
    if (defined('MICRO_ERROR_LOG') && MICRO_ERROR_LOG === false) { return; }

    defined('MICRO_ERROR_LOG_FILE')
    ?
    $log = MICRO_ERROR_LOG_FILE
    :
    $log = WEB_DIR.'/errors_!_!_'.md5(WEB_DIR).'.log';
    $perm = WEB_DIR.'/error_permission_storage_!_!_'.md5(WEB_DIR).'.log';

    if (! $errorlog = @fopen($log, 'ab')) {
        if (! $errorlog = @fopen($perm, 'ab')) {
            $this->sendHeaderMessage('permission');
            return;
        }
        $this->sendHeaderMessage('permission');
    } 
    $time = date('Y m d - H:i:s');

    fwrite($errorlog, '---- '.$time." ------------- ".'['.$code.'] '
            .$name." ----\n\n"
            .$logMess."\n"
            .$file.'::'.$line."\n\n");

    if (defined('MICRO_ERROR_LOG_TRACE') && MICRO_ERROR_LOG_TRACE === 0) {
        $tc = &$this->traceResult['inversion'];
    }
    else {
        $tc = &$this->traceResult['log']; 
    }
    foreach ($tc as $TraceValue) {

        if (empty($TraceValue['args'])) {
            fwrite($errorlog, "                    +\n");
        }

        foreach ($TraceValue['args'] as $ArgsValue) {
            fwrite($errorlog, '                    + '.$ArgsValue."\n");
        }

        fwrite($errorlog, '            {f} '.$TraceValue['function']."\n");
        fwrite($errorlog, '          ====> '.$TraceValue['class']."\n");
        fwrite($errorlog, $TraceValue['line'].' '
                      .$TraceValue['file'].' '
                      .$TraceValue['line']
                      ."\n\n");
    }
    fwrite($errorlog, "\n\n\n");

    fclose($errorlog);