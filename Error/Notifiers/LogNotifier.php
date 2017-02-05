<?php

namespace MicroMir\Error\Notifiers;


class LogNotifier extends AbstractNotifier
{

    protected function display()
    {
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
    }

}