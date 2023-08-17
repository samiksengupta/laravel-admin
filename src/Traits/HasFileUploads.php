<?php

namespace Samik\LaravelAdmin\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait HasFileUploads
{
    protected $deleteQueue;

    /**
     * Boot up the trait
     */
    public static function bootHasFileUploads()
    {
        // hook up the events
        static::saving(function ($model) {
            $model->storeFiles();
        });

        static::deleting(function ($model) {
            $model->deleteFiles();
        });
        
        static::saved(function ($model) {
            $model->processDeleteQueue();
        });

        static::deleted(function ($model) {
            $model->processDeleteQueue();
        });
    }

    public function storeFiles()
    {
        foreach ($this->getDefinedUploadFields() as $key => $val) {
            $folder = $val['folder'] ?? 'uploads';
            $folder = $folder . DIRECTORY_SEPARATOR . date('Y-m');
            $disk = $val['disk'] ?? 'public';
            $field = is_int($key) ? $val : $key;
            // handle base64 inputs
            if (\request()->has($field) && $this->isValidBase64File(\request()->input($field))) {
                $file = request()->input($field);
                $path = $this->storeBase64File($file, $folder, $disk);
                if($path) {
                    $this->deleteFiles();
                    $this->attributes[$field] = $path;
                }
            }
            // handle file inputs
            elseif(request()->hasFile($field)) {
                $files = request()->file($field);
                if(\is_array($files)) {
                    $paths = [];
                    foreach($files as $file) {
                        $path = $file->store($folder, $disk);
                        if($path) $paths[] = $path;
                        else {
                            $paths = [];
                            break;
                        }
                    }
                    if(!empty($paths)) {
                        $this->deleteFiles();
                        $this->attributes[$field] = implode(',', $paths);
                    }
                }
                else {
                    $file = $files;
                    $path = $file->store($folder, $disk);
                    if($path) {
                        $this->deleteFiles();
                        $this->attributes[$field] = $path;
                    }
                }
            }
            // keep original or valid assigned value
            else {
                $value = $this->attributes[$field];
                if(\is_array($value)) {
                    $filtered = array_filter($value);
                    $value = empty($filtered) ? null : implode(',', $filtered);
                }
                $this->attributes[$field] = $value ?? $this->getOriginal($field);
            }
        }
    }

    // adds files to delete queue
    public function deleteFiles(array $fields = null)
    {
        if($this->exists) {
            foreach ($this->getDefinedUploadFields() as $key => $val) {
                $field = is_int($key) ? $val : $key;
                if($fields && !\in_array($field, $fields)) continue;
                $disk = $val['disk'] ?? 'public';
                $existing = $this->getOriginal($field);
                if($existing) {
                    $value = $existing;
                    $files = explode(',', $existing);
                    foreach($files as $index => $file) {
                        if($file) {
                            $this->addToDeleteQueue($file, $disk);
                            unset($files[$index]);
                        }
                    }
                    if(count($files)) $value = implode(',', $files);
                    // $this->attributes[$field] = $value;
                }
            }
        }
    }

    // removes specific file by index from data and adds them to delete queue
    public function removeFile(array $fields = null, int $index = 0)
    {
        if($this->exists) {
            foreach ($this->getDefinedUploadFields() as $key => $val) {
                $field = is_int($key) ? $val : $key;
                if($fields && !\in_array($field, $fields)) continue;
                $disk = $val['disk'] ?? 'public';
                $existing = $this->getOriginal($field);
                if($existing) {
                    $value = $existing;
                    $files = explode(',', $existing);
                    $file = $files[$index] ?? null;
                    if($file) {
                        $this->addToDeleteQueue($file, $disk);
                        unset($files[$index]);
                    }
                    if(count($files)) $value = implode(',', $files);
                    // $this->attributes[$field] = $value;
                }
            }
        }
    }

    public function addToDeleteQueue($path, $disk = 'public')
    {
        if(!\is_array($this->deleteQueue)) $this->deleteQueue = [];
        $this->deleteQueue[$path] = $disk;
    }

    public function removeFromDeleteQueue($path)
    {
        if(!\is_array($this->deleteQueue)) $this->deleteQueue = [];
        else unset($this->deleteQueue[$path]);
    }

    public function processDeleteQueue()
    {
        if(!\is_array($this->deleteQueue)) $this->deleteQueue = [];
        else foreach($this->deleteQueue as $file => $disk) Storage::disk($disk)->delete($file);
    }

    /**
     * Get all the base64 fields defined on model
     *
     * @return array
     */
    public function getDefinedUploadFields()
    {
        $fields = isset($this->uploadFields) && \is_array($this->uploadFields) ? $this->uploadFields : [];

        return $fields;
    }

    // Validates a string as base64 encoded file
    private function isValidBase64File($encoded)
    {
        $hasMimes = preg_match("/(?<=data\:)(.*?)(?=\;base64)/", \is_string($encoded) ? $encoded : '');
        $hasData = $hasMimes && preg_match("/[^,]*$/", $encoded, $data);
        return $hasMimes && $hasData ? (base64_encode(base64_decode($data[0], true)) === $data[0]) : false;
    }

    // Saves and returns the name of a base64 encoded file
    private function storeBase64File($encoded, $folder = 'uploads', $disk = 'public')
    {
        if(!$this->isValidBase64File($encoded)) return null;

        preg_match("/(?<=data\:)(.*?)(?=\;base64)/", $encoded, $mimes);
        preg_match("/[^,]*$/", $encoded, $data);
        
        $extension = $this->mime2ext($mimes[0]);
        if(!$extension) return null;

        $fileName = $folder . DIRECTORY_SEPARATOR . Str::random(16) . '.' . $extension;
        $stored = Storage::disk($disk)->put($fileName, base64_decode($data[0]));
        return $stored ? $fileName : null;
    }

    // Converts mime type to file extension
    private function mime2ext($mime) {
        $mime_map = [
            'video/3gpp2'                                                               => '3g2',
            'video/3gp'                                                                 => '3gp',
            'video/3gpp'                                                                => '3gp',
            'application/x-compressed'                                                  => '7zip',
            'audio/x-acc'                                                               => 'aac',
            'audio/ac3'                                                                 => 'ac3',
            'application/postscript'                                                    => 'ai',
            'audio/x-aiff'                                                              => 'aif',
            'audio/aiff'                                                                => 'aif',
            'audio/x-au'                                                                => 'au',
            'video/x-msvideo'                                                           => 'avi',
            'video/msvideo'                                                             => 'avi',
            'video/avi'                                                                 => 'avi',
            'application/x-troff-msvideo'                                               => 'avi',
            'application/macbinary'                                                     => 'bin',
            'application/mac-binary'                                                    => 'bin',
            'application/x-binary'                                                      => 'bin',
            'application/x-macbinary'                                                   => 'bin',
            'image/bmp'                                                                 => 'bmp',
            'image/x-bmp'                                                               => 'bmp',
            'image/x-bitmap'                                                            => 'bmp',
            'image/x-xbitmap'                                                           => 'bmp',
            'image/x-win-bitmap'                                                        => 'bmp',
            'image/x-windows-bmp'                                                       => 'bmp',
            'image/ms-bmp'                                                              => 'bmp',
            'image/x-ms-bmp'                                                            => 'bmp',
            'application/bmp'                                                           => 'bmp',
            'application/x-bmp'                                                         => 'bmp',
            'application/x-win-bitmap'                                                  => 'bmp',
            'application/cdr'                                                           => 'cdr',
            'application/coreldraw'                                                     => 'cdr',
            'application/x-cdr'                                                         => 'cdr',
            'application/x-coreldraw'                                                   => 'cdr',
            'image/cdr'                                                                 => 'cdr',
            'image/x-cdr'                                                               => 'cdr',
            'zz-application/zz-winassoc-cdr'                                            => 'cdr',
            'application/mac-compactpro'                                                => 'cpt',
            'application/pkix-crl'                                                      => 'crl',
            'application/pkcs-crl'                                                      => 'crl',
            'application/x-x509-ca-cert'                                                => 'crt',
            'application/pkix-cert'                                                     => 'crt',
            'text/css'                                                                  => 'css',
            'text/x-comma-separated-values'                                             => 'csv',
            'text/comma-separated-values'                                               => 'csv',
            'application/vnd.msexcel'                                                   => 'csv',
            'application/x-director'                                                    => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'application/x-dvi'                                                         => 'dvi',
            'message/rfc822'                                                            => 'eml',
            'application/x-msdownload'                                                  => 'exe',
            'video/x-f4v'                                                               => 'f4v',
            'audio/x-flac'                                                              => 'flac',
            'video/x-flv'                                                               => 'flv',
            'image/gif'                                                                 => 'gif',
            'application/gpg-keys'                                                      => 'gpg',
            'application/x-gtar'                                                        => 'gtar',
            'application/x-gzip'                                                        => 'gzip',
            'application/mac-binhex40'                                                  => 'hqx',
            'application/mac-binhex'                                                    => 'hqx',
            'application/x-binhex40'                                                    => 'hqx',
            'application/x-mac-binhex40'                                                => 'hqx',
            'text/html'                                                                 => 'html',
            'image/x-icon'                                                              => 'ico',
            'image/x-ico'                                                               => 'ico',
            'image/vnd.microsoft.icon'                                                  => 'ico',
            'text/calendar'                                                             => 'ics',
            'application/java-archive'                                                  => 'jar',
            'application/x-java-application'                                            => 'jar',
            'application/x-jar'                                                         => 'jar',
            'image/jp2'                                                                 => 'jp2',
            'video/mj2'                                                                 => 'jp2',
            'image/jpx'                                                                 => 'jp2',
            'image/jpm'                                                                 => 'jp2',
            'image/jpeg'                                                                => 'jpeg',
            'image/pjpeg'                                                               => 'jpeg',
            'application/x-javascript'                                                  => 'js',
            'application/json'                                                          => 'json',
            'text/json'                                                                 => 'json',
            'application/vnd.google-earth.kml+xml'                                      => 'kml',
            'application/vnd.google-earth.kmz'                                          => 'kmz',
            'text/x-log'                                                                => 'log',
            'audio/x-m4a'                                                               => 'm4a',
            'application/vnd.mpegurl'                                                   => 'm4u',
            'audio/midi'                                                                => 'mid',
            'application/vnd.mif'                                                       => 'mif',
            'video/quicktime'                                                           => 'mov',
            'video/x-sgi-movie'                                                         => 'movie',
            'audio/mpeg'                                                                => 'mp3',
            'audio/mpg'                                                                 => 'mp3',
            'audio/mpeg3'                                                               => 'mp3',
            'audio/mp3'                                                                 => 'mp3',
            'video/mp4'                                                                 => 'mp4',
            'video/mpeg'                                                                => 'mpeg',
            'application/oda'                                                           => 'oda',
            'audio/ogg'                                                                 => 'ogg',
            'video/ogg'                                                                 => 'ogg',
            'application/ogg'                                                           => 'ogg',
            'application/x-pkcs10'                                                      => 'p10',
            'application/pkcs10'                                                        => 'p10',
            'application/x-pkcs12'                                                      => 'p12',
            'application/x-pkcs7-signature'                                             => 'p7a',
            'application/pkcs7-mime'                                                    => 'p7c',
            'application/x-pkcs7-mime'                                                  => 'p7c',
            'application/x-pkcs7-certreqresp'                                           => 'p7r',
            'application/pkcs7-signature'                                               => 'p7s',
            'application/pdf'                                                           => 'pdf',
            'application/octet-stream'                                                  => 'pdf',
            'file/pdf'                                                                  => 'pdf',
            'application/x-x509-user-cert'                                              => 'pem',
            'application/x-pem-file'                                                    => 'pem',
            'application/pgp'                                                           => 'pgp',
            'application/x-httpd-php'                                                   => 'php',
            'application/php'                                                           => 'php',
            'application/x-php'                                                         => 'php',
            'text/php'                                                                  => 'php',
            'text/x-php'                                                                => 'php',
            'application/x-httpd-php-source'                                            => 'php',
            'image/png'                                                                 => 'png',
            'image/x-png'                                                               => 'png',
            'application/powerpoint'                                                    => 'ppt',
            'application/vnd.ms-powerpoint'                                             => 'ppt',
            'application/vnd.ms-office'                                                 => 'ppt',
            'application/msword'                                                        => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop'                                                   => 'psd',
            'image/vnd.adobe.photoshop'                                                 => 'psd',
            'audio/x-realaudio'                                                         => 'ra',
            'audio/x-pn-realaudio'                                                      => 'ram',
            'application/x-rar'                                                         => 'rar',
            'application/rar'                                                           => 'rar',
            'application/x-rar-compressed'                                              => 'rar',
            'audio/x-pn-realaudio-plugin'                                               => 'rpm',
            'application/x-pkcs7'                                                       => 'rsa',
            'text/rtf'                                                                  => 'rtf',
            'text/richtext'                                                             => 'rtx',
            'video/vnd.rn-realvideo'                                                    => 'rv',
            'application/x-stuffit'                                                     => 'sit',
            'application/smil'                                                          => 'smil',
            'text/srt'                                                                  => 'srt',
            'image/svg+xml'                                                             => 'svg',
            'application/x-shockwave-flash'                                             => 'swf',
            'application/x-tar'                                                         => 'tar',
            'application/x-gzip-compressed'                                             => 'tgz',
            'image/tiff'                                                                => 'tiff',
            'text/plain'                                                                => 'txt',
            'text/x-vcard'                                                              => 'vcf',
            'application/videolan'                                                      => 'vlc',
            'text/vtt'                                                                  => 'vtt',
            'audio/x-wav'                                                               => 'wav',
            'audio/wave'                                                                => 'wav',
            'audio/wav'                                                                 => 'wav',
            'application/wbxml'                                                         => 'wbxml',
            'video/webm'                                                                => 'webm',
            'image/webp'                                                                => 'webp',
            'audio/x-ms-wma'                                                            => 'wma',
            'application/wmlc'                                                          => 'wmlc',
            'video/x-ms-wmv'                                                            => 'wmv',
            'video/x-ms-asf'                                                            => 'wmv',
            'application/xhtml+xml'                                                     => 'xhtml',
            'application/excel'                                                         => 'xl',
            'application/msexcel'                                                       => 'xls',
            'application/x-msexcel'                                                     => 'xls',
            'application/x-ms-excel'                                                    => 'xls',
            'application/x-excel'                                                       => 'xls',
            'application/x-dos_ms_excel'                                                => 'xls',
            'application/xls'                                                           => 'xls',
            'application/x-xls'                                                         => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.ms-excel'                                                  => 'xlsx',
            'application/xml'                                                           => 'xml',
            'text/xml'                                                                  => 'xml',
            'text/xsl'                                                                  => 'xsl',
            'application/xspf+xml'                                                      => 'xspf',
            'application/x-compress'                                                    => 'z',
            'application/x-zip'                                                         => 'zip',
            'application/zip'                                                           => 'zip',
            'application/x-zip-compressed'                                              => 'zip',
            'application/s-compressed'                                                  => 'zip',
            'multipart/x-zip'                                                           => 'zip',
            'text/x-scriptzsh'                                                          => 'zsh',
        ];

        return isset($mime_map[$mime]) === true ? $mime_map[$mime] : false;
    }
}
