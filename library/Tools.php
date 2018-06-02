<?php

class Tools {
    public static function timestampToDatetime ($ts) {
        return date('Y-m-d H:i:s', $ts);
    }

    public static function randPassword () {
        static $set = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.+?';
        return password_hash(str_shuffle($set), PASSWORD_DEFAULT);
    }

    public static function println ($msg, $same_line = FALSE, $data = [], $color = FALSE) {
        static $colors = [
            'red'     => "\e[1;31m",
            'green'   => "\e[1;32m",
            'yellow'  => "\e[1;33m",
            'blue'    => "\e[1;34m",
            'magenta' => "\e[1;35m",
            'cyan'    => "\e[1;36m",
            'end'     => "\e[0m"
        ];
        if ($color)
            $msg = $colors[$color] . $msg . $colors['end'];
        echo sprintf($same_line ? "\r" . $msg : $msg . PHP_EOL, ...$data);
    }

    public static function post($api, $data, $cookie = '') {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1/flarum/api/' . $api);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Cookie: ' . $cookie]);

        $res = curl_exec($ch);

        curl_close($ch);
        return json_decode($res, TRUE);
    }

    public static function generateData($file_name, $data) {
        $fp = fopen(__DIR__ . '/../data/' . $file_name . '.php', 'w+');
        fwrite($fp, '<?php ' . PHP_EOL . 'return ' . var_export($data, TRUE) . ';');
    }

    public static function retrieveData($file_name) {
        return (include __DIR__ . '/../data/' . $file_name . '.php');
    }

    public static function getOldAvatarPath($uid) {
	    $uid  = abs(intval($uid));
	    $uid  = sprintf("%09d", $uid);
	    $dir1 = substr($uid, 0, 3);
	    $dir2 = substr($uid, 3, 2);
	    $dir3 = substr($uid, 5, 2);
	    return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).'_avatar_big.jpg';
    }

    public static function removeDirectory($dir) {
        $it    = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    public static function human_filesize($bytes, $decimals = 2) {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public static function fixPost($str) {
        $patterns = [
            ['/\[table=100%,.+?\]\[tr\]\[td\]\[size=9pt\]\[发帖际遇\]: .+?\[\/font\]\[\/td\]\[\/tr\]\[\/table\]/s', ''],
            ['/\[quote\].+?\[size=\d+\]\[color=#\w+\](.+?) 发表于 .+?\[\/color\] \[url=.+?pid=(\d+)&ptid=\d+\].+?\[\/url\]\[\/size\]\[\/quote\]\s*/s', '@$1#$2 '],
            ['/\[b\]回复 \[url=.+?pid=(\d+)&ptid=\d+\]\d+#\[\/url\] \[i\](.+?)\[\/i\] \[\/b\]\s*/s', '@$2#$1 '],
            ['/\[img=(\d+),(\d+)\]/', '[img width=$1 height=$2]'],
            ['/\[pshuffle=(\d+),(\d+)\](.+?)\[\/pshuffle\]/', '[pshuffle width=$1 height=$2]$3[/pshuffle]'],
            ['/\[tr=(.+?)\]/', '[tr]'],
            ['/\[td=(\d+)%\]/', '[td width=$3]'],
            ['/\[td=(\d+),(\d+)\]/', function ($m) {
                return '[td' . ($m[1] > 1 ? ' colspan=' . $m[1] : '') . ($m[2] > 1 ? ' rowspan=' . $m[2] : '') . ']';
            }],
            ['/\[td=(\d+),(\d+),(\d+)%\]/', '[td colspan=$1 rowspan=$2 width=$3]', function ($m) {
                return '[td' . ($m[1] > 1 ? ' colspan=' . $m[1] : '') . ($m[2] > 1 ? ' rowspan=' . $m[2] : '') . ' width=' . $m[3] . ']';
            }],
            ['/\[table=(\d+)%\]/', '[table]'],
            ['/\[quote\]\[size=2\]\[url=forum\.php\?mod=redirect&goto=findpost&pid=(\d+)&ptid=\d+\]\[color=#999999\](.+?) 发表于.+?\[\/quote\]/s', '@$2#$1'],
            ['/\[\/?(font|p|index|float|backcolor|align|hide)(=.+?)?\]/', ''],
            ['/\[size=(.+?)\]/', function ($m) {
                $m[1] = intval($m[1]);
                $em_map = [null, 0.63, 0.82, 1.0, 1.13, 1.5, 2.0, 3.0];
                return '[size=' . (empty($em_map[$m[1]]) ? '12' : floor($em_map[$m[1]] * 16)) . ']';
            }],
            ['/\[(\/)?media(=.+?)?\]/', '[$1media]'],
            ['/\[page\]/', '[hr]'],
            ['/\[color=rgb\(\s*(\d+),\s*(\d+),\s*(\d+)\)\]/', function ($m) {
                return '[color=#' . str_pad(base_convert($m[1] * 16 * 16 + $m[1] * 16 + $m[2], 10, 16), 6, '0') . ']';
            }]
        ];
        foreach ($patterns as $p)
            $str = (is_callable($p[1]) ? 'preg_replace_callback' : 'preg_replace')($p[0], $p[1], $str);

        return $str;
    }


}
