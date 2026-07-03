<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Xlsx_encryptor
 *
 * Dua mode proteksi file .xlsx:
 *
 * 1. setModifyPassword($filePath, $password)
 *    Password hanya untuk EDIT ("password to modify"): file bisa dibuka
 *    siapa saja secara read-only, tapi Excel minta password untuk write
 *    access. Diimplementasikan lewat elemen <fileSharing> di workbook.xml.
 *
 * 2. encrypt($xlsxBinary, $password)
 *    Password saat BUKA file: enkripsi ECMA-376 Standard Encryption
 *    (AES-128 + SHA-1) dalam container OLE Compound File, sama dengan
 *    fitur "Encrypt with Password" Microsoft Excel 2007+.
 *    Butuh ekstensi PHP: openssl.
 *
 * Pemakaian:
 *   $this->load->library('xlsx_encryptor');
 *   $this->xlsx_encryptor->setModifyPassword($tmpFile, $password);
 *   // atau
 *   $encrypted = $this->xlsx_encryptor->encrypt($xlsxBinary, $password);
 */
class Xlsx_encryptor
{
    const SPIN_COUNT = 50000;
    const KEY_BYTES  = 16; // AES-128

    const SECTOR_SIZE = 512;
    const MINI_SIZE   = 64;
    const MINI_CUTOFF = 4096;
    const FREESECT    = 0xFFFFFFFF;
    const ENDOFCHAIN  = 0xFFFFFFFE;
    const FATSECT     = 0xFFFFFFFD;
    const DIFSECT     = 0xFFFFFFFC;
    const NOSTREAM    = 0xFFFFFFFF;

    /**
     * Set "password to modify" pada file xlsx: file tetap bisa dibuka
     * siapa saja (read-only), tapi Excel minta password untuk edit.
     * File di path diubah langsung (in-place).
     *
     * @param string $filePath path file .xlsx
     * @param string $password password untuk write access
     */
    public function setModifyPassword($filePath, $password)
    {
        $hash = $this->legacyPasswordHash($password);
        $tag  = '<fileSharing userName="WOCS Apps" reservationPassword="' . $hash . '"/>';

        $xml = $this->zipRead($filePath, 'xl/workbook.xml');
        if ($xml === false || $xml === null) {
            throw new Exception('xl/workbook.xml tidak ditemukan di file xlsx');
        }

        // fileSharing harus berada tepat setelah fileVersion (urutan schema CT_Workbook)
        if (strpos($xml, '<fileSharing') !== false) {
            $xml = preg_replace('/<fileSharing[^>]*>/', $tag, $xml, 1);
        } elseif (strpos($xml, '<fileVersion') !== false) {
            $xml = preg_replace('/(<fileVersion[^>]*>)/', '$1' . $tag, $xml, 1);
        } else {
            $xml = preg_replace('/(<workbook[^>]*>)/', '$1' . $tag, $xml, 1);
        }

        $this->zipReplace($filePath, 'xl/workbook.xml', $xml);
    }

    /**
     * Hash password 16-bit legacy Excel (ST_UnsignedShortHex), algoritma yang
     * sama dengan PHPExcel_Shared_PasswordHasher untuk proteksi sheet.
     */
    private function legacyPasswordHash($password)
    {
        $hash = 0x0000;
        $pos  = 1;
        foreach (str_split($password) as $char) {
            $value = ord($char) << $pos++;
            $hash ^= (($value & 0x7fff) | ($value >> 15));
        }
        $hash ^= strlen($password);
        $hash ^= 0xCE4B;
        return strtoupper(dechex($hash));
    }

    private function zipRead($filePath, $entry)
    {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new Exception('Gagal membuka file xlsx: ' . $filePath);
            }
            $content = $zip->getFromName($entry);
            $zip->close();
            return $content;
        }
        $list = $this->pclzip($filePath)->extract(PCLZIP_OPT_BY_NAME, $entry, PCLZIP_OPT_EXTRACT_AS_STRING);
        if (!is_array($list) || empty($list[0]['content'])) {
            return false;
        }
        return $list[0]['content'];
    }

    private function zipReplace($filePath, $entry, $content)
    {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new Exception('Gagal membuka file xlsx: ' . $filePath);
            }
            $zip->addFromString($entry, $content); // menimpa entry lama
            $zip->close();
            return;
        }
        // fallback PclZip: tulis ke file sementara lalu delete + add
        $zip     = $this->pclzip($filePath);
        $tmpDir  = rtrim(sys_get_temp_dir(), '/\\');
        $tmpFile = $tmpDir . DIRECTORY_SEPARATOR . basename($entry);
        file_put_contents($tmpFile, $content);
        $zip->delete(PCLZIP_OPT_BY_NAME, $entry);
        $result = $zip->add($tmpFile, PCLZIP_OPT_REMOVE_PATH, $tmpDir, PCLZIP_OPT_ADD_PATH, dirname($entry));
        @unlink($tmpFile);
        if ($result == 0) {
            throw new Exception('Gagal menulis ulang ' . $entry . ': ' . $zip->errorInfo(true));
        }
    }

    private function pclzip($filePath)
    {
        if (!class_exists('PclZip')) {
            require_once APPPATH . 'libraries/PHPExcel/Shared/PCLZip/pclzip.lib.php';
        }
        return new PclZip($filePath);
    }

    /**
     * Enkripsi isi file xlsx dengan password (password diminta saat file dibuka).
     *
     * @param  string $xlsxData isi binary file .xlsx
     * @param  string $password password buka file
     * @return string           isi binary file terenkripsi (tetap disimpan sebagai .xlsx)
     */
    public function encrypt($xlsxData, $password)
    {
        if (!function_exists('openssl_encrypt')) {
            throw new Exception('Ekstensi PHP openssl dibutuhkan untuk membuat xlsx berpassword');
        }

        $salt = $this->randomBytes(16);
        $key  = $this->deriveKey($password, $salt);

        // Verifier: dipakai Excel untuk mengecek password benar/salah
        $verifier              = $this->randomBytes(16);
        $encryptedVerifier     = $this->aesEcb($verifier, $key);
        $verifierHash          = str_pad(sha1($verifier, true), 32, "\0");
        $encryptedVerifierHash = $this->aesEcb($verifierHash, $key);

        $info = $this->encryptionInfo($salt, $encryptedVerifier, $encryptedVerifierHash);

        // EncryptedPackage: 8 byte ukuran asli + isi terenkripsi AES-128-ECB
        $size = strlen($xlsxData);
        if ($size % 16 !== 0) {
            $xlsxData .= str_repeat("\0", 16 - ($size % 16));
        }
        $package = pack('V', $size) . pack('V', 0) . $this->aesEcb($xlsxData, $key);

        // Urutan stream harus sesuai urutan nama CFB (pendek dulu, lalu abjad)
        return $this->compoundFile(array(
            'EncryptionInfo'   => $info,
            'EncryptedPackage' => $package,
        ));
    }

    /**
     * Derivasi kunci AES dari password sesuai MS-OFFCRYPTO 2.3.4.7
     * (ECMA-376 standard encryption key generation).
     */
    private function deriveKey($password, $salt)
    {
        $hash = sha1($salt . $this->utf16($password), true);
        for ($i = 0; $i < self::SPIN_COUNT; $i++) {
            $hash = sha1(pack('V', $i) . $hash, true);
        }
        $hash = sha1($hash . pack('V', 0), true);

        $x1 = sha1($this->xorFill($hash, 0x36), true);
        return substr($x1, 0, self::KEY_BYTES);
    }

    private function xorFill($hash, $byte)
    {
        $buf = str_repeat(chr($byte), 64);
        $len = strlen($hash);
        for ($i = 0; $i < $len; $i++) {
            $buf[$i] = chr(ord($buf[$i]) ^ ord($hash[$i]));
        }
        return $buf;
    }

    private function aesEcb($data, $key)
    {
        $result = openssl_encrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);
        if ($result === false) {
            throw new Exception('Enkripsi AES gagal: ' . openssl_error_string());
        }
        return $result;
    }

    private function randomBytes($length)
    {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }
        return openssl_random_pseudo_bytes($length);
    }

    private function utf16($string)
    {
        if (function_exists('iconv')) {
            return iconv('UTF-8', 'UTF-16LE', $string);
        }
        return mb_convert_encoding($string, 'UTF-16LE', 'UTF-8');
    }

    /**
     * Stream EncryptionInfo versi 3.2 (standard encryption, AES-128, SHA-1).
     */
    private function encryptionInfo($salt, $encVerifier, $encVerifierHash)
    {
        $csp = $this->utf16('Microsoft Enhanced RSA and AES Cryptographic Provider') . "\0\0";
        // flags, sizeExtra, algId AES-128, algIdHash SHA-1, keySize (bit),
        // providerType PROV_RSA_AES, reserved1, reserved2
        $header = pack('VVVVVVVV', 0x24, 0, 0x0000660E, 0x00008004, 128, 0x18, 0, 0) . $csp;

        return pack('vv', 3, 2)             // versi major.minor
            . pack('V', 0x24)               // flags (fCryptoAPI | fAES)
            . pack('V', strlen($header))
            . $header
            . pack('V', 16) . $salt         // saltSize + salt
            . $encVerifier                  // encryptedVerifier (16 byte)
            . pack('V', 20)                 // verifierHashSize (SHA-1)
            . $encVerifierHash;             // encryptedVerifierHash (32 byte)
    }

    /**
     * Tulis OLE Compound File (CFB v3, sektor 512 byte) berisi stream yang
     * diberikan. Nama stream harus sudah terurut sesuai aturan CFB
     * (panjang nama dulu, lalu perbandingan uppercase).
     */
    private function compoundFile(array $streams)
    {
        $miniData = '';
        $miniFat  = array();
        $entries  = array();

        foreach ($streams as $name => $data) {
            $size = strlen($data);
            if ($size < self::MINI_CUTOFF) {
                // stream kecil masuk mini stream (sektor 64 byte)
                $first = strlen($miniData) / self::MINI_SIZE;
                $count = (int)ceil($size / self::MINI_SIZE);
                $miniData .= str_pad($data, $count * self::MINI_SIZE, "\0");
                for ($i = 1; $i < $count; $i++) {
                    $miniFat[] = $first + $i;
                }
                $miniFat[] = self::ENDOFCHAIN;
                $entries[] = array('name' => $name, 'size' => $size, 'mini' => true, 'first' => $first, 'data' => null);
            } else {
                $entries[] = array('name' => $name, 'size' => $size, 'mini' => false, 'first' => null, 'data' => $data);
            }
        }

        $dirSectors     = (int)ceil((count($entries) + 1) / 4);
        $miniFatSectors = count($miniFat) ? (int)ceil(count($miniFat) / 128) : 0;
        $miniSectors    = (int)ceil(strlen($miniData) / self::SECTOR_SIZE);
        $dataSectors    = 0;
        foreach ($entries as $e) {
            if (!$e['mini']) {
                $dataSectors += (int)ceil($e['size'] / self::SECTOR_SIZE);
            }
        }
        $rest = $dirSectors + $miniFatSectors + $miniSectors + $dataSectors;

        // jumlah sektor FAT (dan DIFAT bila FAT > 109 sektor)
        $fatSectors   = 0;
        $difatSectors = 0;
        do {
            $fatSectors++;
            $difatSectors = ($fatSectors <= 109) ? 0 : (int)ceil(($fatSectors - 109) / 127);
            $total = $rest + $fatSectors + $difatSectors;
        } while ($fatSectors * 128 < $total);

        // penempatan sektor: [DIFAT][FAT][direktori][miniFAT][mini stream][data]
        $next         = $difatSectors + $fatSectors;
        $dirStart     = $next;
        $next        += $dirSectors;
        $miniFatStart = $miniFatSectors ? $next : self::ENDOFCHAIN;
        $next        += $miniFatSectors;
        $miniStart    = $miniSectors ? $next : self::ENDOFCHAIN;
        $next        += $miniSectors;
        foreach ($entries as $k => $e) {
            if (!$e['mini']) {
                $entries[$k]['first'] = $next;
                $next += (int)ceil($e['size'] / self::SECTOR_SIZE);
            }
        }

        // tabel FAT
        $fat = array_fill(0, $fatSectors * 128, self::FREESECT);
        for ($i = 0; $i < $difatSectors; $i++) {
            $fat[$i] = self::DIFSECT;
        }
        for ($i = 0; $i < $fatSectors; $i++) {
            $fat[$difatSectors + $i] = self::FATSECT;
        }
        $this->chain($fat, $dirStart, $dirSectors);
        if ($miniFatSectors) {
            $this->chain($fat, $miniFatStart, $miniFatSectors);
        }
        if ($miniSectors) {
            $this->chain($fat, $miniStart, $miniSectors);
        }
        foreach ($entries as $e) {
            if (!$e['mini']) {
                $this->chain($fat, $e['first'], (int)ceil($e['size'] / self::SECTOR_SIZE));
            }
        }

        // header 512 byte
        $out = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"
            . str_repeat("\0", 16)                  // CLSID
            . pack('v', 0x003E)                     // minor version
            . pack('v', 0x0003)                     // major version 3 (sektor 512)
            . pack('v', 0xFFFE)                     // byte order
            . pack('v', 9)                          // sector shift (512)
            . pack('v', 6)                          // mini sector shift (64)
            . str_repeat("\0", 6)
            . pack('V', 0)                          // jumlah sektor direktori (0 di v3)
            . pack('V', $fatSectors)
            . pack('V', $dirStart)
            . pack('V', 0)                          // transaction signature
            . pack('V', self::MINI_CUTOFF)
            . pack('V', $miniFatStart)
            . pack('V', $miniFatSectors)
            . pack('V', $difatSectors ? 0 : self::ENDOFCHAIN)
            . pack('V', $difatSectors);
        for ($i = 0; $i < 109; $i++) {
            $out .= pack('V', $i < $fatSectors ? $difatSectors + $i : self::FREESECT);
        }

        // sektor DIFAT (hanya jika FAT > 109 sektor)
        for ($d = 0; $d < $difatSectors; $d++) {
            for ($k = 0; $k < 127; $k++) {
                $idx = 109 + $d * 127 + $k;
                $out .= pack('V', $idx < $fatSectors ? $difatSectors + $idx : self::FREESECT);
            }
            $out .= pack('V', ($d + 1 < $difatSectors) ? $d + 1 : self::ENDOFCHAIN);
        }

        // sektor FAT
        foreach ($fat as $val) {
            $out .= pack('V', $val);
        }

        // direktori
        $dir = $this->dirEntry('Root Entry', 5, 1, self::NOSTREAM, self::NOSTREAM,
            count($entries) ? 1 : self::NOSTREAM, $miniStart, strlen($miniData));
        $n = count($entries);
        foreach ($entries as $k => $e) {
            $right = ($k + 1 < $n) ? $k + 2 : self::NOSTREAM;
            $dir  .= $this->dirEntry($e['name'], 2, 1, self::NOSTREAM, $right, self::NOSTREAM, $e['first'], $e['size']);
        }
        while (strlen($dir) % self::SECTOR_SIZE !== 0) {
            $dir .= $this->dirEntry('', 0, 0, self::NOSTREAM, self::NOSTREAM, self::NOSTREAM, 0, 0);
        }
        $out .= $dir;

        // miniFAT
        if ($miniFatSectors) {
            $mf = '';
            foreach ($miniFat as $val) {
                $mf .= pack('V', $val);
            }
            $out .= str_pad($mf, $miniFatSectors * self::SECTOR_SIZE, pack('V', self::FREESECT));
        }

        // isi mini stream
        if ($miniSectors) {
            $out .= str_pad($miniData, $miniSectors * self::SECTOR_SIZE, "\0");
        }

        // stream besar
        foreach ($entries as $e) {
            if (!$e['mini']) {
                $sectors = (int)ceil($e['size'] / self::SECTOR_SIZE);
                $out    .= str_pad($e['data'], $sectors * self::SECTOR_SIZE, "\0");
            }
        }

        return $out;
    }

    private function chain(array &$fat, $start, $count)
    {
        for ($i = 0; $i < $count - 1; $i++) {
            $fat[$start + $i] = $start + $i + 1;
        }
        $fat[$start + $count - 1] = self::ENDOFCHAIN;
    }

    private function dirEntry($name, $type, $color, $left, $right, $child, $start, $size)
    {
        $nameUtf16 = ($name === '') ? '' : $this->utf16($name) . "\0\0";
        return str_pad($nameUtf16, 64, "\0")
            . pack('v', strlen($nameUtf16))         // panjang nama (byte, termasuk null)
            . chr($type) . chr($color)
            . pack('VVV', $left, $right, $child)
            . str_repeat("\0", 16)                  // CLSID
            . pack('V', 0)                          // state bits
            . str_repeat("\0", 16)                  // created + modified
            . pack('V', $start)
            . pack('V', $size) . pack('V', 0);      // ukuran 64-bit (high dword 0)
    }
}
