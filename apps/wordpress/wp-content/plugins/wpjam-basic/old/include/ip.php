<?php 

class IP{
    private static $ip = null;
    private static $fp = null;
    private static $offset = null;
    private static $index = null;
    private static $cached = array();

    public static function find($ip){
        if (empty( $ip ) === true) {
            return 'N/A';
        }

        $nip   = gethostbyname($ip);
        $ipdot = explode('.', $nip);

        if ($ipdot[0] < 0 || $ipdot[0] > 255 || count($ipdot) !== 4) {
            return 'N/A';
        }

        if (isset( self::$cached[$nip] ) === true) {
            return self::$cached[$nip];
        }

        if (self::$fp === null) {
            self::init();
        }

        $nip2 = pack('N', ip2long($nip));

        $tmp_offset = (int) $ipdot[0] * 4;
        $start      = unpack('Vlen',
            self::$index[$tmp_offset].self::$index[$tmp_offset + 1].self::$index[$tmp_offset + 2].self::$index[$tmp_offset + 3]);

        $index_offset = $index_length = null;
        $max_comp_len = self::$offset['len'] - 1024 - 4;
        for ($start = $start['len'] * 8 + 1024; $start < $max_comp_len; $start += 8) {
            if (self::$index{$start}.self::$index{$start + 1}.self::$index{$start + 2}.self::$index{$start + 3} >= $nip2) {
                $index_offset = unpack('Vlen',
                    self::$index{$start + 4}.self::$index{$start + 5}.self::$index{$start + 6}."\x0");
                $index_length = unpack('Clen', self::$index{$start + 7});

                break;
            }
        }

        if ($index_offset === null) {
            return 'N/A';
        }

        fseek(self::$fp, self::$offset['len'] + $index_offset['len'] - 1024);

        self::$cached[$nip] = explode("\t", fread(self::$fp, $index_length['len']));

        return self::$cached[$nip];
    }

    private static function init(){
        if (self::$fp === null) {
            self::$ip = new self();

            self::$fp = fopen(__DIR__.'/static/17monipdb.dat', 'rb');
            if (self::$fp === false) {
                throw new Exception('Invalid 17monipdb.dat file!');
            }

            self::$offset = unpack('Nlen', fread(self::$fp, 4));
            if (self::$offset['len'] < 4) {
                throw new Exception('Invalid 17monipdb.dat file!');
            }

            self::$index = fread(self::$fp, self::$offset['len'] - 4);
        }
    }

    public function __destruct(){
        if (self::$fp !== null) {
            fclose(self::$fp);
        }
    }
}