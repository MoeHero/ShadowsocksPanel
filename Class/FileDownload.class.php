<?php
class FileDownload {
    /**
     * 下载指定文件
     * @param  string $filepath 文件路径
     * @param  string $filename 保存文件名
     * @return array
     */
    public static function download($filepath, $filename = '') {
        Debug::offDebugInfo();
        set_time_limit(0);
        session_write_close();
        if(file_exists($filepath)) {
            $fp = fopen($filepath, 'rb');
            $filesize = filesize($filepath);
            $range = FileDownload::getRange($filesize);
            if(!empty($range)) {
                extract($range);
                header('HTTP/1.1 206 Partial Content');
                header("Content-Range: bytes {$start}-{$end}/{$filesize}");
                fseek($fp, sprintf('%u', $start));
                $filesize = $end - $start + 1;
            } else {
                header('HTTP/1.1 200 OK');
            }
            if($filename == "") $filename = basename($filepath);
            header('Accept-Ranges: bytes');
            header("Content-Length: {$filesize}");
            header('Content-Type: application/octet-stream');
            header("Content-Disposition: attachment; filename={$filename}");
            header('Content-Transfer-Encoding: binary');
            while(!feof($fp)) echo fread($fp, 1024 * 1024 * 10);//10MB
            ($fp != null) && fclose($fp);
        }
    }

    private static function getRange($filesize) {
        if(isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            $range = preg_replace('/[\s,].*/', '', $range);
            $range = explode('-', substr($range, 6));
            if(count($range) < 2) $range[1] = $filesize;
            $range = array_combine(array('start', 'end'), $range);
            if(empty($range['start'])) $range['start'] = 0;
            if(empty($range['end'])) $range['end'] = $filesize;
            return $range;
        }
        return null;
    }
}